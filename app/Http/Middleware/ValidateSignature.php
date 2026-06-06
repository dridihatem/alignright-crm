<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\URL;

class ValidateSignature
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->hasValidSignature()) {
            throw new HttpException(403, 'Invalid or expired link.');
        }

        return $next($request);
    }
}