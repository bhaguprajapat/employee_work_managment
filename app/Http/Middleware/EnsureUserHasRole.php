<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route-level role gate, e.g. ->middleware('role:admin,manager').
 * Fine-grained record checks still happen in policies.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        $allowed = array_map(
            fn (string $role): UserRole => UserRole::from($role),
            $roles
        );

        if ($user === null || ! in_array($user->role, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }
}
