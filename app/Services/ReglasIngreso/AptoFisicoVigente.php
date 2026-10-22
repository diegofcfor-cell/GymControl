<?php

namespace App\Services\ReglasIngreso;

use App\Models\Socio;

class AptoFisicoVigente implements ReglaIngreso
{
    public function motivoDeRechazo(Socio $socio): ?string
    {
        return $socio->aptoVigente() ? null : 'Apto físico vencido o no presentado.';
    }
}
