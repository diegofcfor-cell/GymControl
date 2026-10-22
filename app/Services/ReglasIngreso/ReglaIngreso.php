<?php

namespace App\Services\ReglasIngreso;

use App\Models\Socio;

/**
 * Estrategia de validación del ingreso: cada regla decide una sola condición.
 * Para sumar una condición se crea otra clase y se agrega a la lista de AppServiceProvider.
 */
interface ReglaIngreso
{
    /** Motivo del rechazo que se le muestra al socio, o null si la regla se cumple. */
    public function motivoDeRechazo(Socio $socio): ?string;
}
