<?php

namespace App\Listeners;

use App\Events\AccesoDenegado;
use App\Events\EgresoRegistrado;
use App\Events\IngresoRegistrado;
use Illuminate\Support\Facades\Log;

/**
 * Observador de la pantalla de acceso: deja constancia de cada ingreso, egreso y rechazo.
 *
 * Reglas:
 * - Solo se registra el id del socio, nunca su nombre, DNI ni PIN (minimización de datos, Ley 25.326).
 * - Un rechazo guarda los motivos, para poder explicar después una decisión automatizada (art. 1757 CCyC).
 * - Laravel lo registra solo (descubrimiento de eventos) por el tipo del parámetro de handle().
 */
class RegistrarAccesoEnLog
{
    public function handle(IngresoRegistrado|EgresoRegistrado|AccesoDenegado $evento): void
    {
        [$mensaje, $contexto] = match (true) {
            $evento instanceof IngresoRegistrado => ['Acceso: ingreso', [
                'socio_id' => $evento->asistencia->socio_id,
                'hora' => $evento->asistencia->hora_ingreso,
            ]],
            $evento instanceof EgresoRegistrado => ['Acceso: egreso', [
                'socio_id' => $evento->asistencia->socio_id,
                'hora' => $evento->asistencia->hora_salida,
                'permanencia' => $evento->asistencia->tiempo_permanencia,
            ]],
            $evento instanceof AccesoDenegado => ['Acceso: denegado', [
                'socio_id' => $evento->socio->id,
                'motivos' => $evento->motivos,
            ]],
        };

        Log::info($mensaje, $contexto);
    }
}
