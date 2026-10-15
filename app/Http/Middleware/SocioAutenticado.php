<?php

namespace App\Http\Middleware;

use App\Models\Socio;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Portal del socio: la sesión guarda solo el id del socio (clave "socio_id"),
 * separada del login de operadores. Uso: ->middleware('socio')
 */
class SocioAutenticado
{
    public function handle(Request $request, Closure $next): Response
    {
        $socio = Socio::find($request->session()->get('socio_id'));

        if (! $socio || ! $socio->estaActivo()) {
            $request->session()->forget('socio_id');

            return redirect()->route('portal.login');
        }

        $request->attributes->set('socio', $socio);

        return $next($request);
    }
}
