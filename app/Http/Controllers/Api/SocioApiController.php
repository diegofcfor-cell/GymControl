<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginSocioRequest;
use App\Http\Resources\AsistenciaResource;
use App\Http\Resources\SocioResource;
use App\Services\AutenticacionSocioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API del portal del socio con tokens de Laravel Sanctum:
 *  - login con DNI + PIN devuelve un token Bearer que vence a los TOKEN_DIAS días.
 *  - El socio solo consulta sus propios datos (todo sale de $request->user()).
 *  - logout revoca el token usado en la petición.
 */
class SocioApiController extends Controller
{
    public const TOKEN_DIAS = 30;

    public function login(LoginSocioRequest $request, AutenticacionSocioService $autenticacion): JsonResponse
    {
        ['socio' => $socio, 'error' => $error] = $autenticacion->intentar($request->dni, $request->pin);

        if (! $socio) {
            return response()->json(['message' => $error], 401);
        }

        $token = $socio->createToken(
            $request->input('dispositivo', 'api'),
            ['socio'],
            now()->addDays(self::TOKEN_DIAS),
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'tipo' => 'Bearer',
            'vence' => $token->accessToken->expires_at->toIso8601String(),
        ], 201);
    }

    public function perfil(Request $request): SocioResource
    {
        return new SocioResource($request->user()->load('membresia'));
    }

    public function asistencias(Request $request)
    {
        $filtros = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);

        $asistencias = $request->user()->asistencias()
            ->when($filtros['desde'] ?? null, fn ($q, $d) => $q->whereDate('fecha', '>=', $d))
            ->when($filtros['hasta'] ?? null, fn ($q, $h) => $q->whereDate('fecha', '<=', $h))
            ->orderByDesc('fecha')->orderByDesc('hora_ingreso')
            ->paginate(20)->withQueryString();

        return AsistenciaResource::collection($asistencias);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada.']);
    }
}
