<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Optional cron HTTP hardening.
 * When env CRON_SECRET is empty: allow (backward compatible).
 * When set: require matching ?cron_secret= or X-Cron-Secret header.
 */
class VerifyCronSecret
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = (string) env('CRON_SECRET', '');

        if ($secret === '') {
            return $next($request);
        }

        $provided = (string) ($request->query('cron_secret')
            ?? $request->header('X-Cron-Secret')
            ?? '');

        if (!hash_equals($secret, $provided)) {
            return response()->json([
                'status'  => 403,
                'message' => 'Forbidden',
            ], 403);
        }

        return $next($request);
    }
}
