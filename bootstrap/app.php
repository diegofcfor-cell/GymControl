<?php

use App\Http\Middleware\RolMiddleware;
use App\Http\Middleware\SocioAutenticado;
use App\Http\Middleware\SocioConToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Render termina TLS en su balanceador: confiar en los encabezados X-Forwarded-*
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'rol' => RolMiddleware::class,
            'socio' => SocioAutenticado::class,
            'socio.token' => SocioConToken::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // La API siempre responde en JSON (401, 403, 422...), aunque el cliente no envíe Accept: application/json.
        $exceptions->shouldRenderJsonWhen(fn ($request) => $request->is('api/*') || $request->expectsJson());
    })->create();
