<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Uso: ->middleware('rol:administrador')  o  'rol:administrador,recepcionista' */
class RolMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->activo) {
            auth()->logout();

            return redirect()->route('login')->withErrors(['email' => 'Su usuario está inactivo.']);
        }

        if (! in_array($user->rol, $roles, true)) {
            abort(403, 'No tiene permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
