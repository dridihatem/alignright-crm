<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $supportedLocales = ['en', 'fr'];

        // The global default comes from the database setting (managed by admin)
        $default = Setting::getValue('language', config('app.locale', 'fr'));

        // A per-user choice stored in the session takes priority over the default,
        // so doctors/technicians/labs can switch their own language.
        $language = Session::get('locale', $default);

        // Validate the language exists in our supported locales
        if (!in_array($language, $supportedLocales)) {
            $language = in_array($default, $supportedLocales) ? $default : 'fr';
        }

        // Set the application locale
        App::setLocale($language);

        return $next($request);
    }
}