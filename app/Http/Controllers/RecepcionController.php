<?php

namespace App\Http\Controllers;

use App\Repositories\AsistenciaRepository;

/**
 * Panel de recepción (HU29, Figura 3 de la maquetación aprobada).
 *
 * Reglas:
 * - Es la pantalla de inicio del recepcionista: buscador rápido de socios, socios en sala y accesos del día.
 * - No muestra importes ni indicadores económicos, que son exclusivos del administrador (Tabla 1 del Plan).
 */
class RecepcionController extends Controller
{
    public function index(AsistenciaRepository $asistencias)
    {
        return view('recepcion.index', [
            'presentes' => $asistencias->presentesHoy(),
            'ultimosAccesos' => $asistencias->ultimosAccesosHoy(),
        ]);
    }
}
