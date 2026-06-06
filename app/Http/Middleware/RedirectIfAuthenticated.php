<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Redirect based on user role
                $user = Auth::user();
                if ($user->isAdmin()) {
                    return redirect('/admin');
                }

                elseif ($user->isDoctor()) {
                    return redirect('/doctor');
                }  
                elseif ($user->isTechnician()) {
                    return redirect('/technician');
                }
                elseif ($user->isLaboratory()) {
                    return redirect('/laboratory');
                   
                }
               
            }
        }

        return $next($request);
    }
} 