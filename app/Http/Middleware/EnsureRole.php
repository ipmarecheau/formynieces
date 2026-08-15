<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Role-gates a route: only users whose `role` is in the allowed list may proceed. Used to keep
 * students, guardians and admins on their own routes (the admin panel is gated separately by
 * User::canAccessPanel). Usage: `->middleware('role:guardian')` or `role:student,admin`.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_if($user === null || ! in_array($user->role, $roles, true), 403);

        return $next($request);
    }
}
