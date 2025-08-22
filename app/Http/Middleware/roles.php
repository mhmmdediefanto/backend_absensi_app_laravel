<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class roles
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, ...$roles)
    {
        $userRole = $request->user()->roles;

        if (!in_array($userRole, $roles)) {
            return response()->json(['message' => "You are not authorized to access this resource {$userRole}."], 403);
        }

        return $next($request);
    }
}
