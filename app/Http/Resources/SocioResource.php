<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Datos que el socio ve de sí mismo en la API (sin PIN ni código QR). */
class SocioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $apto = $this->ultimoApto();

        return [
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'dni' => $this->dni,
            'membresia' => $this->membresia?->nombre,
            'cuota_vencimiento' => $this->fecha_vencimiento?->toDateString(),
            'cuota_al_dia' => $this->cuotaAlDia(),
            'apto_vencimiento' => $apto?->fecha_vencimiento?->toDateString(),
            'apto_vigente' => $this->aptoVigente(),
        ];
    }
}
