<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EsSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->esSuperAdmin()) {
            return $next($request);
        }

        abort(403, 'Solo el superadmin puede realizar esta acción.');
    }
}
