<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale =
            $request->user()?->locale
            ?? $request->header('X-Locale')
            ?? $request->input('locale')
            ?? config('app.locale');

        if (!in_array($locale, ['zh_TW', 'en', 'ja'])) {
            $locale = 'zh_TW';
        }

        App::setLocale($locale);

        return $next($request);
    }
}