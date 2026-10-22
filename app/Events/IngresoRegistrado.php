<?php

namespace App\Events;

use App\Models\Asistencia;
use Illuminate\Foundation\Events\Dispatchable;

/** Un socio ingresó a la sala: se creó su asistencia de hoy. */
class IngresoRegistrado
{
    use Dispatchable;

    public function __construct(public readonly Asistencia $asistencia) {}
}
