<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Redirect based on user role
        $user = Auth::user();

        if ($user->isDoctor()) {
            return redirect('/doctor');
        } elseif ($user->isAdmin()) {
            return redirect('/admin');
        } elseif ($user->isTechnician()) {
            return redirect('/technician');
        } elseif ($user->isLaboratory()) {
            return redirect('/laboratory');
        } elseif ($user->isCommercial()) {
            return redirect('/commercial');
        }
        
        // Default redirect if role is not recognized
        return redirect('/');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
