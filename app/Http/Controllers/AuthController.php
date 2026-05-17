<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantLineChannel;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Services\AI\TenantDifyProvisioningService;


class AuthController extends Controller
{

    public function __construct(
        protected TenantDifyProvisioningService $tenantDifyProvisioningService
    ) {}

    public function register(Request $request)
    {
        $locale = $this->localeForRequest($request);
        $validator = Validator::make($request->all(), [
            'company_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'locale' => ['nullable', 'string', 'in:zh_TW,en'],
        ], $this->registerValidationMessages($locale));

        if ($validator->fails()) {
            return response()->json([
                'message' => $this->authMessage('register_failed', $locale),
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $result = DB::transaction(function () use ($validated) {
            $locale = $validated['locale'] ?? request()->header('X-Locale') ?? 'zh_TW';
            $locale = in_array($locale, ['zh_TW', 'en'], true) ? $locale : 'zh_TW';
            $baseSlug = Str::slug($validated['company_name']);
            $slug = $baseSlug ?: 'tenant';
            $counter = 1;

            while (Tenant::where('slug', $slug)->exists()) {
                $slug = ($baseSlug ?: 'tenant') . '-' . $counter;
                $counter++;
            }

            $tenant = Tenant::create([
                'name' => $validated['company_name'],
                'slug' => $slug,
                'company_name' => $validated['company_name'],
                'status' => Tenant::STATUS_ACTIVE,
                'timezone' => 'Asia/Taipei',
                'locale' => $locale,
                'currency' => 'TWD',
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => User::ROLE_FREE,
                'status' => User::STATUS_ACTIVE,
                'locale' => $locale,
            ]);

            $tenant->update([
                'owner_user_id' => $user->id,
                'contact_name' => $user->name,
                'contact_email' => $user->email,
            ]);

            TenantLineChannel::firstOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'provider' => 'line',
                    'is_active' => false,
                    'is_verified' => false,
                    'webhook_url' => rtrim(config('app.url'), '/') . '/api/line/webhook/' . $tenant->webhook_key,
                ]
            );

            return compact('tenant', 'user');
        });

        $emailVerificationDeliveryStatus = 'sent';

        try {
            $result['user']->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            report($e);
            Log::error('Email verification notification failed during registration', [
                'user_id' => $result['user']->id,
                'email' => $result['user']->email,
                'error' => $e->getMessage(),
            ]);
            $emailVerificationDeliveryStatus = 'failed';
        }

        $aiSetting = null;
        $provisioningStatus = 'skipped';

        if (config('services.dify.enabled')) {
            try {
                $aiSetting = $this->tenantDifyProvisioningService->provision($result['tenant']);
                $provisioningStatus = 'ready';
            } catch (\Throwable $e) {
                report($e);
                Log::error('Tenant Dify provisioning failed', [
                    'tenant_id' => $result['tenant']->id,
                    'error' => $e->getMessage(),
                ]);
                $provisioningStatus = 'failed';
            }
        }

        $token = $result['user']->createToken('lineai-web')->plainTextToken;
        AuthAnalyticsController::record($result['user'], \App\Models\AuthEvent::TYPE_REGISTER, 'email', $request);

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $result['user'],
            'tenant' => $result['tenant'],
            'dify_dataset_id' => $aiSetting?->dify_dataset_id,
            'dify_provisioning_status' => $provisioningStatus,
            'email_verification_required' => true,
            'email_verification_delivery_status' => $emailVerificationDeliveryStatus,
        ], 201);
    }




    public function login(Request $request)
    {
        

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($validated)) {
            return response()->json([
                'message' => '帳號或密碼錯誤',
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->status !== User::STATUS_ACTIVE) {

            Auth::logout();

            return response()->json([
                'message' => '帳號目前不可登入',
            ], 403);
        }

        $user->update([
            'last_login_at' => now(),
        ]);

        $token = $user->createToken('lineai-web')->plainTextToken;
        AuthAnalyticsController::record($user, \App\Models\AuthEvent::TYPE_LOGIN, 'email', $request);

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user,
            'email_verified' => !is_null($user->email_verified_at),
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()?->load('tenant');

        return response()->json([
            'user' => $user,
            'email_verified' => !is_null($user?->email_verified_at),
        ]);
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()?->delete();
        }

        return response()->json([
            'success' => true,
        ]);
        
    }


    public function updateLocale(Request $request)
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', 'in:zh_TW,en,ja'],
        ]);

        $user = $request->user();
        $user->update([
              'locale' => $validated['locale'],
       ]);

        return response()->json([
            'success' => true,
            'locale' => $user->locale,
        ]);
    }

    private function localeForRequest(Request $request): string
    {
        $locale = $request->input('locale')
            ?? $request->header('X-Locale')
            ?? 'zh_TW';

        return $locale === 'en' ? 'en' : 'zh_TW';
    }

    private function registerValidationMessages(string $locale): array
    {
        $messages = [
            'zh_TW' => [
                'company_name.required' => '請輸入公司名稱',
                'company_name.string' => '公司名稱格式不正確',
                'company_name.max' => '公司名稱最多 255 個字元',
                'name.required' => '請輸入姓名',
                'name.string' => '姓名格式不正確',
                'name.max' => '姓名最多 255 個字元',
                'email.required' => '請輸入 Email',
                'email.email' => '請輸入有效的 Email',
                'email.max' => 'Email 最多 255 個字元',
                'email.unique' => '此 Email 已註冊',
                'password.required' => '請輸入密碼',
                'password.string' => '密碼格式不正確',
                'password.min' => '密碼至少需要 8 個字元',
                'password.confirmed' => '兩次輸入的密碼不一致',
                'locale.in' => '語系設定不正確',
            ],
            'en' => [
                'company_name.required' => 'Please enter your company name.',
                'company_name.string' => 'The company name format is invalid.',
                'company_name.max' => 'The company name may not be greater than 255 characters.',
                'name.required' => 'Please enter your name.',
                'name.string' => 'The name format is invalid.',
                'name.max' => 'The name may not be greater than 255 characters.',
                'email.required' => 'Please enter your email.',
                'email.email' => 'Please enter a valid email address.',
                'email.max' => 'The email may not be greater than 255 characters.',
                'email.unique' => 'This email is already registered.',
                'password.required' => 'Please enter your password.',
                'password.string' => 'The password format is invalid.',
                'password.min' => 'Password must be at least 8 characters.',
                'password.confirmed' => 'The password confirmation does not match.',
                'locale.in' => 'The selected language is invalid.',
            ],
        ];

        return $messages[$locale] ?? $messages['zh_TW'];
    }

    private function authMessage(string $key, string $locale): string
    {
        $messages = [
            'zh_TW' => [
                'register_failed' => '註冊失敗，請確認資料後再試',
            ],
            'en' => [
                'register_failed' => 'Registration failed. Please check your information and try again.',
            ],
        ];

        return $messages[$locale][$key] ?? $messages['zh_TW'][$key] ?? $key;
    }


}
