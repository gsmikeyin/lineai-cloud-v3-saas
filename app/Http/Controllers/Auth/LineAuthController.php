<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;


class LineAuthController extends Controller
{
    public function redirect(Request $request)
    {       
        $state = Str::random(40);
        error_log( $state);
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


       // Log::error("callback " . ((string) session('line_login_state')));
      Log::error("callback 0");

        abort_unless(
            hash_equals((string) session('line_login_state'), (string) $request->state),
            403,
            'Invalid LINE login state.'
        );


        Log::error("callback-1");

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

        Log::error("callback0");

        $idToken = $tokenResponse['id_token'] ?? null;
        $accessToken = $tokenResponse['access_token'] ?? null;

        abort_unless($idToken && $accessToken, 400, 'LINE token response invalid.');

        // 官方提供 verify endpoint，可直接驗證 id_token 並拿 profile/email
        $verifyResponse = Http::asForm()->post(
            'https://api.line.me/oauth2/v2.1/verify',
            [
                'id_token' => $idToken,
                'client_id' => config('services.line_login.channel_id'),
            ]
        )->throw()->json();

        Log::error("callback1");

        $lineUserId = $verifyResponse['sub'] ?? null;
        $displayName = $verifyResponse['name'] ?? null;
        $picture = $verifyResponse['picture'] ?? null;
        $email = $verifyResponse['email'] ?? null;

        // 若 verify 沒回完整 profile，可再用 access token 打 profile endpoint
        if (!$displayName || !$lineUserId) {
            $profileResponse = Http::withToken($accessToken)
                ->get('https://api.line.me/v2/profile')
                ->throw()
                ->json();

            $lineUserId = $profileResponse['userId'] ?? $lineUserId;
            $displayName = $profileResponse['displayName'] ?? $displayName;
            $picture = $profileResponse['pictureUrl'] ?? $picture;
        }

        Log::error("callback2");

        abort_unless($lineUserId, 400, 'Unable to retrieve LINE user ID.');

        $result = DB::transaction(function () use ($lineUserId, $displayName, $picture, $email) {
            $user = User::query()
                ->where('line_user_id', $lineUserId)
                ->orWhere(function ($q) use ($email) {
                    if ($email) {
                        $q->where('email', $email);
                    }
                })
                ->first();

            if (!$user) {
                // 單租戶 / demo 版：若沒有 tenant，就掛到第一個 active tenant
                $tenant = Tenant::query()->orderBy('id')->first();

                abort_unless($tenant, 500, 'No tenant available for LINE login.');

                $user = User::create([
                    'tenant_id' => $tenant->id,
                    'name' => $displayName ?: 'LINE User',
                    'email' => $email ?: ('line_' . Str::lower(Str::random(12)) . '@line.local'),
                    'password' => Str::random(32),
                    'role' => User::ROLE_OWNER ?? 'owner',
                    'status' => User::STATUS_ACTIVE ?? 'active',
                    'line_user_id' => $lineUserId,
                    'avatar' => $picture,
                    'email_verified_at' => $email ? now() : null,
                ]);
            } else {
                $user->update([
                    'line_user_id' => $lineUserId,
                    'avatar' => $picture ?: $user->avatar,
                    'name' => $displayName ?: $user->name,
                ]);
            }

            // CRM 綁定：若同 tenant 下沒有 customer，就自動建立
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

            return compact('user', 'token');
        });

        return redirect(
            rtrim(config('app.frontend_url'), '/') . '/login/success?token=' . urlencode($result['token'])
        );
    }
}