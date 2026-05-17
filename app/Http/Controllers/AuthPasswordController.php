<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthPasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $locale = $this->localeForRequest($request);
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'locale' => ['nullable', 'string', 'in:zh_TW,en'],
        ], $this->validationMessages($locale));

        if ($validator->fails()) {
            return response()->json([
                'message' => $this->message('forgot_failed', $locale),
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        try {
            $status = Password::sendResetLink([
                'email' => $validated['email'],
            ]);
        } catch (\Throwable $e) {
            report($e);
            Log::error('Password reset link notification failed', [
                'email' => $validated['email'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => $this->message('forgot_failed', $locale),
            ], 500);
        }

        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => $this->passwordStatusMessage($status, $locale),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $this->passwordStatusMessage($status, $locale),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $locale = $this->localeForRequest($request);
        $validator = Validator::make($request->all(), [
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'locale' => ['nullable', 'string', 'in:zh_TW,en'],
        ], $this->validationMessages($locale));

        if ($validator->fails()) {
            return response()->json([
                'message' => $this->message('reset_failed', $locale),
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        unset($validated['locale']);

        $status = Password::reset(
            $validated,
            function ($user, $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => $this->passwordStatusMessage($status, $locale),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $this->passwordStatusMessage($status, $locale),
        ]);
    }

    private function localeForRequest(Request $request): string
    {
        $locale = $request->input('locale')
            ?? $request->header('X-Locale')
            ?? 'zh_TW';

        return $locale === 'en' ? 'en' : 'zh_TW';
    }

    private function validationMessages(string $locale): array
    {
        $messages = [
            'zh_TW' => [
                'token.required' => '重設密碼連結不完整，請重新申請。',
                'token.string' => '重設密碼連結格式不正確。',
                'email.required' => '請輸入 Email',
                'email.email' => '請輸入有效的 Email',
                'password.required' => '請輸入密碼',
                'password.string' => '密碼格式不正確',
                'password.min' => '密碼至少需要 8 個字元',
                'password.confirmed' => '兩次輸入的密碼不一致',
                'locale.in' => '語系設定不正確',
            ],
            'en' => [
                'token.required' => 'The reset password link is incomplete. Please request a new one.',
                'token.string' => 'The reset password link format is invalid.',
                'email.required' => 'Please enter your email.',
                'email.email' => 'Please enter a valid email address.',
                'password.required' => 'Please enter your password.',
                'password.string' => 'The password format is invalid.',
                'password.min' => 'Password must be at least 8 characters.',
                'password.confirmed' => 'The password confirmation does not match.',
                'locale.in' => 'The selected language is invalid.',
            ],
        ];

        return $messages[$locale] ?? $messages['zh_TW'];
    }

    private function passwordStatusMessage(string $status, string $locale): string
    {
        $messages = [
            'zh_TW' => [
                Password::RESET_LINK_SENT => '已寄出重設密碼信件',
                Password::PASSWORD_RESET => '密碼已重設',
                Password::INVALID_USER => '找不到此 Email 對應的帳號',
                Password::INVALID_TOKEN => '重設密碼連結無效或已過期，請重新申請。',
                Password::RESET_THROTTLED => '重設密碼信件已寄出，請稍後再試。',
            ],
            'en' => [
                Password::RESET_LINK_SENT => 'Password reset email sent.',
                Password::PASSWORD_RESET => 'Password has been reset.',
                Password::INVALID_USER => 'We could not find an account with that email address.',
                Password::INVALID_TOKEN => 'The reset password link is invalid or expired. Please request a new one.',
                Password::RESET_THROTTLED => 'A password reset email was already sent. Please try again later.',
            ],
        ];

        return $messages[$locale][$status] ?? $status;
    }

    private function message(string $key, string $locale): string
    {
        $messages = [
            'zh_TW' => [
                'forgot_failed' => '寄送失敗，請確認 Email 後再試',
                'reset_failed' => '重設失敗，請確認資料後再試',
            ],
            'en' => [
                'forgot_failed' => 'Failed to send reset link. Please check your email and try again.',
                'reset_failed' => 'Password reset failed. Please check your information and try again.',
            ],
        ];

        return $messages[$locale][$key] ?? $messages['zh_TW'][$key] ?? $key;
    }
}
