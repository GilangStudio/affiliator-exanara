<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global Middleware
        $middleware->web(append: [
            \App\Http\Middleware\MaintenanceMiddleware::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'active.user' => \App\Http\Middleware\ActiveUserMiddleware::class,
            'maintenance' => \App\Http\Middleware\MaintenanceMiddleware::class,
            'check.project.affiliator' => \App\Http\Middleware\CheckProjectAffiliatorMiddleware::class,
            'approved.project' => \App\Http\Middleware\EnsureProjectApproved::class,
        ]);

        $middleware->group('affiliator', [
            'active.user',
            'role:affiliator',
        ]);

        // Middleware Groups
        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\MaintenanceMiddleware::class, // Tambahkan di sini
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
