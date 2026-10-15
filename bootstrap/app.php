<?php

use App\Http\Middleware\RolMiddleware;
use App\Http\Middleware\SocioAutenticado;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Render termina TLS en su balanceador: confiar en los encabezados X-Forwarded-*
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'rol' => RolMiddleware::class,
            'socio' => SocioAutenticado::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
