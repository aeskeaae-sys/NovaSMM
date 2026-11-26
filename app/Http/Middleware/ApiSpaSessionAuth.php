<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiSpaSessionAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // Allow session-authenticated SPA requests to hit /api/* routes that expect auth:sanctum.
        if ($request->user() === null && auth('web')->check()) {
            $request->setUserResolver(fn () => auth('web')->user());
            auth()->setUser(auth('web')->user());
        }
        return $next($request);
    }
}
