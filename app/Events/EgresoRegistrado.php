<?php

namespace App\Events;

use App\Models\Asistencia;
use Illuminate\Foundation\Events\Dispatchable;

/** Un socio salió de la sala: su asistencia ya tiene hora de salida y permanencia. */
class EgresoRegistrado
{
    use Dispatchable;

    public function __construct(public readonly Asistencia $asistencia) {}
}
