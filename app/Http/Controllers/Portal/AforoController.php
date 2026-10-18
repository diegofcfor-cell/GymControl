<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\AforoService;
use Illuminate\Http\Request;

class AforoController extends Controller
{
    public function index(Request $request, AforoService $aforo)
    {
        $dia = (int) $request->integer('dia', today()->dayOfWeek);
        $dia = $dia >= 0 && $dia <= 6 ? $dia : today()->dayOfWeek;

        $mapa = $aforo->mapaSemanal();
        $horas = $mapa[$dia];

        return view('portal.aforo', [
            'mapa' => $mapa,
            'maxMapa' => max(1, ...array_map('max', $mapa)),
            'masConcurridas' => $aforo->franjasMasConcurridas($mapa),
            'dia' => $dia,
            'horas' => $horas,
            'maxHoras' => max(1, max($horas)),
            'capacidad' => $aforo->capacidad(),
            'dias' => ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
        ]);
    }
}
