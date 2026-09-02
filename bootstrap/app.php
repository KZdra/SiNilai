<?php

use App\Http\Middleware\RoleCheck;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'roleCheck' => RoleCheck::class,
            'checkClass' => \App\Http\Middleware\CheckAssignedClass::class,
            'cbt.auth' => \App\Http\Middleware\CbtSyncAuth::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'sso/slo',
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
