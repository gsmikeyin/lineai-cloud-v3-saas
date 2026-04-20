<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantLineChannel;
use App\Models\TenantAiSetting;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\AI\DifyDatasetProvisionService;


class AuthController extends Controller
{

    public function __construct(
        protected DifyDatasetProvisionService $difyDatasetProvisionService
    ) {}

    
    public function register(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $result = DB::transaction(function () use ($validated) {
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
                'locale' => 'zh_TW',
                'currency' => 'TWD',
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => User::ROLE_OWNER,
                'status' => User::STATUS_ACTIVE,
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
                    'channel_name' => $tenant->name . ' Bot',
                    'is_active' => false,
                    'is_verified' => false,
                    'webhook_url' => rtrim(config('app.url'), '/') . '/api/line/webhook/' . $tenant->webhook_key,
                ]
            );


            // 1) 先建立 tenant_ai_settings
            $aiSetting = TenantAiSetting::create([
                'tenant_id' => $tenant->id,
                'provider' => 'dify',
                'dify_base_url' => config('services.dify.base_url'),
                // 註冊時先只寫 dataset_api_key，app_api_key 之後再由管理者填
                'dify_dataset_api_key' => config('services.dify.dataset_api_key'),
                'is_active' => false,
            ]);


            
            // 2) 自動建立空的 Dify Knowledge Base
            $dataset = $this->difyDatasetProvisionService->createEmptyDataset(
                baseUrl: config('services.dify.base_url'),
                datasetApiKey: config('services.dify.dataset_api_key'),
                name: $tenant->name . ' Knowledge Base',
                description: 'Auto-created at registration for tenant #' . $tenant->id,
                indexingTechnique: 'high_quality'
            );

            $aiSetting->update([
                'dify_dataset_id' => $dataset['id'],
                'dify_dataset_name' => $dataset['name'],
            ]);




            event(new Registered($user));

            $token = $user->createToken('lineai-web')->plainTextToken;

            return compact('tenant', 'user', 'token');
        });

        return response()->json([
            'success' => true,
            'token' => $result['token'],
            'user' => $result['user'],
            'tenant' => $result['tenant'],
            'email_verification_required' => true,
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
        $user = $request->user();

        /*
        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }*/

        if ($user) {
            $user->tokens()->delete();
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


}
