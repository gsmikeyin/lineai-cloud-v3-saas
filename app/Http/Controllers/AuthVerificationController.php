<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class AuthVerificationController extends Controller
{
    public function send(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => $this->message('already_verified', $this->localeForRequest($request, $user)),
            ]);
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            report($e);
            Log::error('Email verification notification failed during resend', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $this->message('send_failed', $this->localeForRequest($request, $user)),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => $this->message('sent', $this->localeForRequest($request, $user)),
        ]);
    }

    public function verify(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required'],
            'hash' => ['required', 'string'],
            'expires' => ['required'],
            'signature' => ['required', 'string'],
        ]);

        $signedInUser = $request->user();

        if ($signedInUser && (string) $signedInUser->getKey() !== (string) $validated['id']) {
            return response()->json([
                'message' => $this->message('wrong_user', $this->localeForRequest($request, $signedInUser)),
            ], 403);
        }

        $user = $signedInUser ?: User::query()->find($validated['id']);
        $locale = $this->localeForRequest($request, $user);

        if (! $user) {
            return response()->json([
                'message' => $this->message('user_not_found', $locale),
            ], 404);
        }

        $verifyUrl = url('/api/email/verify/' . $validated['id'] . '/' . $validated['hash'])
            . '?expires=' . urlencode($validated['expires'])
            . '&signature=' . urlencode($validated['signature']);

        if (! URL::hasValidSignature(Request::create($verifyUrl))) {
            return response()->json([
                'message' => $this->message('invalid_or_expired', $locale),
            ], 403);
        }

        if (! hash_equals((string) $validated['hash'], sha1($user->getEmailForVerification()))) {
            return response()->json([
                'message' => $this->message('invalid_data', $locale),
            ], 403);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return response()->json([
            'success' => true,
            'message' => $this->message('verified', $locale),
            'user' => $user->fresh(),
        ]);
    }

    public function verifySigned(Request $request, string $id, string $hash)
    {
        $user = User::query()->find($id);
        $frontendUrl = rtrim($request->getSchemeAndHttpHost(), '/') . '/app/verify-email';

        if (str_contains($frontendUrl, '127.0.0.1') || str_contains($frontendUrl, 'localhost')) {
            $frontendUrl = rtrim(config('app.url'), '/') . '/app/verify-email';
        }

        if (! $user) {
            return redirect()->away($frontendUrl . '?verified=failed');
        }

        $locale = $user->locale === 'en' ? 'en' : 'zh_TW';

        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return redirect()->away($frontendUrl . '?verified=failed&locale=' . urlencode($locale));
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect()->away($frontendUrl . '?verified=1&locale=' . urlencode($locale));
    }

    private function localeForRequest(Request $request, ?User $user = null): string
    {
        $locale = $user?->locale
            ?? $request->user()?->locale
            ?? $request->header('X-Locale')
            ?? $request->input('locale')
            ?? 'zh_TW';

        return $locale === 'en' ? 'en' : 'zh_TW';
    }

    private function message(string $key, string $locale): string
    {
        $messages = [
            'zh_TW' => [
                'already_verified' => 'Email 已完成驗證。',
                'sent' => '驗證信已寄出。',
                'send_failed' => '驗證信寄送失敗，請稍後再試或聯絡管理員。',
                'wrong_user' => '此驗證連結不屬於目前登入的使用者。',
                'user_not_found' => '找不到使用者。',
                'invalid_or_expired' => '驗證連結無效或已過期。',
                'invalid_data' => '驗證資料無效。',
                'verified' => 'Email 驗證成功。',
            ],
            'en' => [
                'already_verified' => 'Email is already verified.',
                'sent' => 'Verification email sent.',
                'send_failed' => 'Failed to send verification email. Please try again later or contact an administrator.',
                'wrong_user' => 'This verification link does not belong to the signed-in user.',
                'user_not_found' => 'User not found.',
                'invalid_or_expired' => 'The verification link is invalid or expired.',
                'invalid_data' => 'The verification data is invalid.',
                'verified' => 'Email verified successfully.',
            ],
        ];

        return $messages[$locale][$key] ?? $messages['zh_TW'][$key] ?? $key;
    }
}
