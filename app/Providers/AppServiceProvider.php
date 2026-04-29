<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
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
                . '&hash=' . sha1($notifiable->getEmailForVerification());
        });
    }
}
