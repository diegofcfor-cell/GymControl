<?php

namespace App\Http\Middleware;

use App\Models\Socio;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API del socio: después de `auth:sanctum`, exige que el token sea de un socio activo
 * (un token de otro tipo de usuario no sirve). Uso: ->middleware(['auth:sanctum', 'socio.token'])
 */
class SocioConToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $socio = $request->user();

        if (! $socio instanceof Socio || ! $socio->estaActivo()) {
            return response()->json(['message' => 'Token inválido para el portal del socio.'], 403);
        }

        return $next($request);
    }
}
