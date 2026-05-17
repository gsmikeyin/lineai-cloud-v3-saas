<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\AuthAnalyticsController;
use App\Models\AuthEvent;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\TenantLineChannel;
use App\Models\User;
use App\Services\AI\TenantDifyProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LineAuthController extends Controller
{
    public function __construct(
        protected TenantDifyProvisioningService $tenantDifyProvisioningService
    ) {}

    public function redirect(Request $request)
    {
        abort_unless(
            config('services.line_login.channel_id') &&
            config('services.line_login.channel_secret') &&
            config('services.line_login.redirect'),
            500,
            'LINE Login is not configured.'
        );

        $state = Str::random(40);
        session(['line_login_state' => $state]);

        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => config('services.line_login.channel_id'),
            'redirect_uri' => config('services.line_login.redirect'),
            'state' => $state,
            'scope' => 'profile openid email',
        ]);

        return redirect('https://access.line.me/oauth2/v2.1/authorize?' . $query);
    }

    public function callback(Request $request)
    {
        abort_unless(
            $request->filled('code') && $request->filled('state'),
            400,
            'Missing LINE callback parameters.'
        );

        abort_unless(
            hash_equals((string) session('line_login_state'), (string) $request->state),
            403,
            'Invalid LINE login state.'
        );

        $tokenResponse = Http::asForm()->post(
            'https://api.line.me/oauth2/v2.1/token',
            [
                'grant_type' => 'authorization_code',
                'code' => $request->code,
                'redirect_uri' => config('services.line_login.redirect'),
                'client_id' => config('services.line_login.channel_id'),
                'client_secret' => config('services.line_login.channel_secret'),
            ]
        )->throw()->json();

        $idToken = $tokenResponse['id_token'] ?? null;
        $accessToken = $tokenResponse['access_token'] ?? null;

        abort_unless($idToken && $accessToken, 400, 'LINE token response invalid.');

        $verifyResponse = Http::asForm()->post(
            'https://api.line.me/oauth2/v2.1/verify',
            [
                'id_token' => $idToken,
                'client_id' => config('services.line_login.channel_id'),
            ]
        )->throw()->json();

        $lineUserId = $verifyResponse['sub'] ?? null;
        $displayName = $verifyResponse['name'] ?? null;
        $picture = $verifyResponse['picture'] ?? null;
        $email = $verifyResponse['email'] ?? null;

        if (!$displayName || !$lineUserId) {
            $profileResponse = Http::withToken($accessToken)
                ->get('https://api.line.me/v2/profile')
                ->throw()
                ->json();

            $lineUserId = $profileResponse['userId'] ?? $lineUserId;
            $displayName = $profileResponse['displayName'] ?? $displayName;
            $picture = $profileResponse['pictureUrl'] ?? $picture;
        }

        abort_unless($lineUserId, 400, 'Unable to retrieve LINE user ID.');

        $result = DB::transaction(function () use ($lineUserId, $displayName, $picture, $email) {
            $user = User::query()
                ->where('line_user_id', $lineUserId)
                ->when($email, fn ($query) => $query->orWhere('email', $email))
                ->first();

            $tenant = null;
            $shouldProvisionDify = false;

            if (!$user) {
                $tenant = $this->createTenantForLineUser($displayName, $email);
                $shouldProvisionDify = true;

                $user = User::create([
                    'tenant_id' => $tenant->id,
                    'name' => $displayName ?: 'LINE User',
                    'email' => $email ?: ('line_' . Str::lower(Str::random(12)) . '@line.local'),
                    'password' => Str::random(32),
                    'role' => User::ROLE_FREE,
                    'status' => User::STATUS_ACTIVE,
                    'line_user_id' => $lineUserId,
                    'avatar' => $picture,
                    'email_verified_at' => now(),
                ]);

                $tenant->update([
                    'owner_user_id' => $user->id,
                    'contact_name' => $user->name,
                    'contact_email' => $user->email,
                ]);

                $lineChannel = TenantLineChannel::firstOrCreate(
                    ['tenant_id' => $tenant->id],
                    [
                        'provider' => 'line',
                        'is_active' => false,
                        'is_verified' => false,
                        'webhook_url' => rtrim(config('app.url'), '/') . '/api/line/webhook/' . $tenant->webhook_key,
                    ]
                );
                $this->clearLineBotGeneratedDefaults($tenant, $lineChannel);
            } else {
                $user->update([
                    'line_user_id' => $lineUserId,
                    'avatar' => $picture ?: $user->avatar,
                    'name' => $displayName ?: $user->name,
                    'email_verified_at' => $user->email_verified_at ?: now(),
                ]);

                $tenant = $user->tenant;
                $shouldProvisionDify = $tenant && ! $tenant->aiSetting()->exists();

                if ($tenant?->lineChannel) {
                    $this->clearLineBotGeneratedDefaults($tenant, $tenant->lineChannel);
                }
            }

            if ($user->tenant_id) {
                Customer::firstOrCreate(
                    [
                        'tenant_id' => $user->tenant_id,
                        'line_user_id' => $lineUserId,
                    ],
                    [
                        'display_name' => $displayName ?: $user->name,
                        'email' => $email,
                        'avatar_url' => $picture,
                        'status' => 'active',
                        'language' => 'zh_TW',
                    ]
                );
            }

            $token = $user->createToken('line-login')->plainTextToken;
            $user->update(['last_login_at' => now()]);

            return compact('user', 'token', 'tenant', 'shouldProvisionDify');
        });
        AuthAnalyticsController::record($result['user'], AuthEvent::TYPE_LOGIN, 'line', $request);

        if ($result['shouldProvisionDify'] && $result['tenant'] && config('services.dify.enabled')) {
            try {
                $this->tenantDifyProvisioningService->provision($result['tenant']);
            } catch (\Throwable $e) {
                report($e);
                Log::error('LINE login tenant Dify provisioning failed', [
                    'tenant_id' => $result['tenant']->id,
                    'user_id' => $result['user']->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return redirect(
            $this->frontendUrl() . '/login/success?token=' . urlencode($result['token'])
        );
    }

    protected function createTenantForLineUser(?string $displayName, ?string $email): Tenant
    {
        $companyName = $displayName ? "{$displayName} Workspace" : 'LINE Workspace';
        $baseSlug = Str::slug($companyName) ?: 'line-workspace';
        $slug = $baseSlug;
        $counter = 1;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return Tenant::create([
            'name' => $companyName,
            'slug' => $slug,
            'company_name' => $companyName,
            'contact_name' => $displayName,
            'contact_email' => $email,
            'status' => Tenant::STATUS_ACTIVE,
            'timezone' => 'Asia/Taipei',
            'locale' => 'zh_TW',
            'currency' => 'TWD',
        ]);
    }

    protected function frontendUrl(): string
    {
        $frontendUrl = rtrim(config('app.frontend_url') ?: config('app.url'), '/');

        if (str_contains($frontendUrl, '127.0.0.1') || str_contains($frontendUrl, 'localhost')) {
            return rtrim(config('app.url'), '/');
        }

        return $frontendUrl;
    }

    protected function clearLineBotGeneratedDefaults(Tenant $tenant, TenantLineChannel $lineChannel): void
    {
        $updates = [];

        if ($lineChannel->channel_name === $tenant->name . ' Bot') {
            $updates['channel_name'] = null;
        }

        if (config('services.line_login.channel_id') && $lineChannel->channel_id === config('services.line_login.channel_id')) {
            $updates['channel_id'] = null;
        }

        if (config('services.line_login.channel_secret') && $lineChannel->channel_secret === config('services.line_login.channel_secret')) {
            $updates['channel_secret'] = null;
        }

        if ($updates) {
            $lineChannel->update($updates);
        }
    }
}
