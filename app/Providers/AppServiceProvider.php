<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();

        RateLimiter::for('cbt-sync', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip())->response(function (Request $request, array $headers) {
                Log::channel('cbt_sync')->warning('CBT Sync Rate limit exceeded (30 req/min)', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Too Many Requests. Rate limit exceeded (max 30 requests per minute).',
                ], 429, $headers);
            });
        });
    }
}
