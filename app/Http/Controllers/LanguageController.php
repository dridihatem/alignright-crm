<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Switch the application language
     */
    public function switch($locale)
    {
        // Validate the locale
        $supportedLocales = ['en', 'fr'];
        
        if (!in_array($locale, $supportedLocales)) {
            return redirect()->back()->with('error', 'Unsupported language');
        }
        
        // Set the application locale
        App::setLocale($locale);
        
        // Store in session
        Session::put('locale', $locale);
        
        // If user is admin, also update the database setting
        if (auth()->check() && auth()->user()->role_id === 1) {
            try {
                \App\Models\Setting::setValue('language', $locale);
            } catch (\Exception $e) {
                // Silently handle database update failure
            }
        }
        
        return redirect()->back()->with('success', __('master.language_changed_successfully'));
    }
}