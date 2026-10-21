<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AsistenciaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'fecha' => $this->fecha->toDateString(),
            'hora_ingreso' => $this->hora_ingreso,
            'hora_salida' => $this->hora_salida,
            'minutos_permanencia' => $this->minutosPermanencia(),
        ];
    }
}
