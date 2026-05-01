<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = ['id', 'en'];
        $locale = (string) $request->session()->get('locale', config('app.locale', 'id'));

        if (!in_array($locale, $supportedLocales, true)) {
            $locale = 'id';
        }

        app()->setLocale($locale);
        \Carbon\Carbon::setLocale($locale);

        view()->share('activeLocale', $locale);

        return $next($request);
    }
}
