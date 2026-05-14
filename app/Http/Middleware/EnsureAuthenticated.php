<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('auth_user')) {
            return redirect()->route('login.form')->with('error', 'Silakan login terlebih dahulu.');
        }

        return $next($request);
    }
}
