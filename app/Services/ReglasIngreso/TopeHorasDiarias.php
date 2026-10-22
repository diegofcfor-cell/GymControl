<?php

namespace App\Services\ReglasIngreso;

use App\Models\Socio;
use App\Repositories\AsistenciaRepository;

/** Un plan con tope de horas diarias no deja volver a entrar a quien ya lo alcanzó hoy. Premium no tiene tope. */
class TopeHorasDiarias implements ReglaIngreso
{
    public function __construct(private AsistenciaRepository $asistencias) {}

    public function motivoDeRechazo(Socio $socio): ?string
    {
        $max = $socio->membresia?->horas_diarias_max;

        return $max !== null && $this->asistencias->minutosHoy($socio) >= $max * 60
            ? 'Alcanzó el tope de horas diarias de su membresía.'
            : null;
    }
}
