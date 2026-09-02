<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CbtSyncAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = config('services.cbt.sync_token') ?: env('CBT_SYNC_TOKEN');
        $token = $request->bearerToken();

        if (empty($token) || empty($expectedToken) || !hash_equals((string) $expectedToken, (string) $token)) {
            Log::channel('cbt_sync')->warning('CBT Sync: Unauthorized access attempt', [
                'ip' => $request->ip(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'has_bearer_token' => !empty($token),
                'status' => 401,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $startTime = microtime(true);
        $response = $next($request);
        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        Log::channel('cbt_sync')->info('CBT Sync: API request processed', [
            'ip' => $request->ip(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'query' => $request->query(),
            'status' => $response->getStatusCode(),
            'duration_ms' => $durationMs,
        ]);

        return $response;
    }
}
