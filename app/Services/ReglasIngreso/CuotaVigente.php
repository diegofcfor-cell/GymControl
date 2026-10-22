<?php

namespace App\Services\ReglasIngreso;

use App\Models\Socio;

class CuotaVigente implements ReglaIngreso
{
    public function motivoDeRechazo(Socio $socio): ?string
    {
        return $socio->cuotaAlDia() ? null : 'Cuota vencida: regularice su pago en recepción.';
    }
}
