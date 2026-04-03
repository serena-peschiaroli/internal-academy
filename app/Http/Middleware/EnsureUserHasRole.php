<?php

namespace App\Http\Middleware;

use App\RoleType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        if ($roles === []) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $allowedRoles = collect($roles)
            ->map(fn (string $role) => RoleType::tryFrom($role))
            ->filter()
            ->values();

        if ($allowedRoles->isEmpty()) {
            abort(Response::HTTP_FORBIDDEN);
        }

        if (! $allowedRoles->contains($user->role?->key)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}

