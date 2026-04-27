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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\AI\DifyDatasetProvisionService;
use App\Services\DifyAppDeployService;
use App\Models\DifyAppPool;


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

            return compact('tenant', 'user');
        });

        event(new Registered($result['user']));

        $aiSetting = null;
        $provisioningStatus = 'skipped';

        if (config('services.dify.enabled')) {
            try {
                $aiSetting = $this->provisionDifyForTenant($result['tenant']);
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

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $result['user'],
            'tenant' => $result['tenant'],
            'dify_dataset_id' => $aiSetting?->dify_dataset_id,
            'dify_provisioning_status' => $provisioningStatus,
            'email_verification_required' => true,
        ], 201);
    }

    protected function provisionDifyForTenant(Tenant $tenant): TenantAiSetting
    {
        $datasetName = "{$tenant->name} KB ({$tenant->id})";

        $dataset = $this->difyDatasetProvisionService->createEmptyDataset(
            name: $datasetName,
            description: "Tenant {$tenant->id} knowledge base"
        );

        $difyApp = $this->createTenantApp([
            'id' => $tenant->id,
            'name' => "CHATBOT{$tenant->name}-{$tenant->id}",
            'name_title' => "CHATBOT_{$tenant->contact_name}-{$tenant->contact_email}",
            'dataset_id' => $dataset['id'],
        ]);

        $pool = DifyAppPool::create([
            'app_name' => $difyApp['app_name'],
            'app_api_key' => $difyApp['api_key'],
            'app_mode' => 'chat',
            'status' => 'assigned',
            'assigned_tenant_id' => $tenant->id,
        ]);

        return TenantAiSetting::updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'provider' => 'dify',
                'dify_dataset_id' => $dataset['id'],
                'dify_dataset_name' => $dataset['name'],
                'dify_app_api_key' => $pool->app_api_key,
                'dify_app_name' => $pool->app_name,
                'dify_app_mode' => $pool->app_mode,
                'is_active' => true,
                'dataset_bound' => false,
            ]
        );
    }
    

    public function createTenantApp(array $tenantData)
    {
        $service = app(DifyAppDeployService::class);

        return $service->deployApp(

            inputDsl: storage_path('app/dify/template.yml'),
            outputDsl: storage_path("app/dify/output/tenant_{$tenantData['id']}.yml"),
            
            datasetIds: [$tenantData['dataset_id']],
            name: "{$tenantData['name_title']}",            
            description: "Tenant {$tenantData['name']} AI App",

            keyName: "tenant-{$tenantData['id']}-key"
        );
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
        if ($request->user()) {
        $request->user()->currentAccessToken()?->delete();
       }

       auth()->guard('web')->logout();

       $request->session()->invalidate();
       $request->session()->regenerateToken();

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
