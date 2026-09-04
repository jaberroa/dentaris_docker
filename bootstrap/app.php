<?php

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
        // Middleware global
        $middleware->web(append: [
            \App\Http\Middleware\LogUserActivity::class,
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        // Middleware de API
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Alias de middleware personalizados
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            'activity' => \App\Http\Middleware\LogUserActivity::class,
            'clinic.context' => \App\Http\Middleware\ResolveClinicContext::class,
            'clinic.selection' => \App\Http\Middleware\ShareClinicSelection::class,
            'clinic.domain.ready' => \App\Http\Middleware\EnsureClinicOwnedDomainReady::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
