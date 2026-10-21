<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AforoService;
use Illuminate\Http\JsonResponse;

/** Aforo público: no expone datos personales, solo cantidades. */
class AforoApiController extends Controller
{
    public function show(AforoService $aforo): JsonResponse
    {
        return response()->json([
            'data' => $aforo->estado() + [
                'franjas_mas_concurridas' => $aforo->franjasMasConcurridas($aforo->mapaSemanal()),
                'actualizado' => now()->toIso8601String(),
            ],
        ]);
    }
}
