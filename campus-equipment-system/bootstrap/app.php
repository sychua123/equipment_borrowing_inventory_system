<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Module 4 (Lai Chee Kit): short names used in the route files
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'module.key' => \App\Http\Middleware\VerifyModuleApiKey::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Module 4: errors on /api/* and on JSON requests are always sent as JSON
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
