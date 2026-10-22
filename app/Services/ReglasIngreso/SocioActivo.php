<?php

namespace App\Services\ReglasIngreso;

use App\Models\Socio;

class SocioActivo implements ReglaIngreso
{
    public function motivoDeRechazo(Socio $socio): ?string
    {
        return $socio->estaActivo() ? null : 'Socio dado de baja o inactivo.';
    }
}
