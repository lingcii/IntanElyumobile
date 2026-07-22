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
        // CORS — global so preflight OPTIONS requests get handled correctly
        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);

        $middleware->api(prepend: [
            \Illuminate\Session\Middleware\StartSession::class,
        ]);

        $middleware->alias([
            'tourist.auth'  => \App\Http\Middleware\TouristAuthenticate::class,
            'auth.throttle' => \App\Http\Middleware\ThrottleAuthRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
