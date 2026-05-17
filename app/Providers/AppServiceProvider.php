<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        VerifyEmail::createUrlUsing(function ($notifiable) {
            $frontendUrl = rtrim(config('app.frontend_url') ?: config('app.url'), '/');
            $appUrl = rtrim(config('app.url'), '/');

            if (str_contains($frontendUrl, '127.0.0.1') || str_contains($frontendUrl, 'localhost')) {
                $frontendUrl = $appUrl;
            }

            $verifyUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );

            $parts = parse_url($verifyUrl);

            return $frontendUrl
                . '/app/verify-email?'
                . ($parts['query'] ?? '')
                . '&id=' . $notifiable->getKey()
                . '&hash=' . sha1($notifiable->getEmailForVerification())
                . '&locale=' . urlencode($notifiable->locale === 'en' ? 'en' : 'zh_TW');
        });

        VerifyEmail::toMailUsing(function ($notifiable, string $url) {
            $locale = $notifiable->locale === 'en' ? 'en' : 'zh_TW';

            if ($locale === 'en') {
                return (new MailMessage)
                    ->subject('Verify your ServiceAI Cloud email')
                    ->greeting('Hello!')
                    ->line('Please verify your email address to enable protected settings and account features.')
                    ->action('Verify Email', $url)
                    ->line('This verification link will expire in 60 minutes.')
                    ->line('If you did not create this account, no further action is required.');
            }

            return (new MailMessage)
                ->subject('驗證你的 ServiceAI Cloud Email')
                ->greeting('您好！')
                ->line('請完成 Email 驗證，才能使用受保護的設定與帳號功能。')
                ->action('驗證 Email', $url)
                ->line('此驗證連結將於 60 分鐘後失效。')
                ->line('如果你沒有註冊此帳號，請忽略這封信。');
        });

        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $locale = $notifiable->locale === 'en' ? 'en' : 'zh_TW';
            $expireMinutes = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');
            $resetUrl = rtrim(config('app.frontend_url') ?: config('app.url'), '/')
                . '/reset-password?token=' . urlencode($token)
                . '&email=' . urlencode($notifiable->getEmailForPasswordReset())
                . '&locale=' . urlencode($locale);

            if ($locale === 'en') {
                return (new MailMessage)
                    ->subject('Reset your ServiceAI Cloud password')
                    ->greeting('Hello!')
                    ->line('We received a request to reset the password for your ServiceAI Cloud account.')
                    ->action('Reset Password', $resetUrl)
                    ->line("This password reset link will expire in {$expireMinutes} minutes.")
                    ->line('If you did not request a password reset, no further action is required.');
            }

            return (new MailMessage)
                ->subject('重設你的 ServiceAI Cloud 密碼')
                ->greeting('您好！')
                ->line('我們收到你的 ServiceAI Cloud 帳號密碼重設申請。')
                ->action('重設密碼', $resetUrl)
                ->line("此重設密碼連結將於 {$expireMinutes} 分鐘後失效。")
                ->line('如果你沒有申請重設密碼，請忽略這封信。');
        });
    }
}
