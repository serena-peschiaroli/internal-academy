<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsSecured
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->first_access) {
            return $next($request);
        }

        if ($request->routeIs('secure-password.*') || $request->routeIs('logout')) {
            return $next($request);
        }

        return redirect()->route('secure-password.edit');
    }
}
