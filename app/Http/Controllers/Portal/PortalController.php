<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Repositories\AsistenciaRepository;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    public function inicio(Request $request, AsistenciaRepository $repositorio)
    {
        $socio = $request->attributes->get('socio')->load('membresia');

        $asistencias = $socio->asistencias()
            ->orderByDesc('fecha')->orderByDesc('hora_ingreso')
            ->limit(20)->get();

        $dentro = $repositorio->estaDentro($socio);

        $delMes = $socio->asistencias()
            ->whereBetween('fecha', [today()->startOfMonth(), today()->endOfMonth()])
            ->get();

        return view('portal.inicio', [
            'socio' => $socio,
            'apto' => $socio->ultimoApto(),
            'asistencias' => $asistencias,
            'dentro' => $dentro,
            'visitasMes' => $delMes->count(),
            'horasMes' => round($delMes->sum(fn ($a) => $a->minutosPermanencia() ?? 0) / 60, 1),
        ]);
    }
}
