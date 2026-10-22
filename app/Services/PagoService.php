<?php

namespace App\Services;

use App\Models\Membresia;
use App\Models\Pago;
use App\Models\Socio;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PagoService
{
    /**
     * Registra un pago y actualiza la membresía y el vencimiento del socio.
     * Si la cuota vigente todavía no venció, el nuevo período comienza al día siguiente del vencimiento.
     */
    public function registrar(Socio $socio, Membresia $membresia, string $metodo, User $operador, ?Carbon $fechaPago = null): Pago
    {
        return DB::transaction(function () use ($socio, $membresia, $metodo, $operador, $fechaPago) {
            $fechaPago = ($fechaPago ?? today())->copy()->startOfDay();
            ['desde' => $desde, 'hasta' => $hasta] = $this->calcularPeriodo($fechaPago, $socio->fecha_vencimiento, $membresia->duracion_dias);

            $pago = Pago::create([
                'socio_id' => $socio->id,
                'membresia_id' => $membresia->id,
                'user_id' => $operador->id,
                'importe' => $membresia->precio,
                'fecha_pago' => $fechaPago->toDateString(),
                'periodo_desde' => $desde->toDateString(),
                'periodo_hasta' => $hasta->toDateString(),
                'metodo_pago' => $metodo,
            ]);

            $socio->update([
                'membresia_id' => $membresia->id,
                'fecha_vencimiento' => $hasta->toDateString(),
                'estado' => 'activo',
            ]);

            return $pago;
        });
    }

    /**
     * Período que cubre un pago: si la cuota vigente vence el día del pago o después, empieza al día siguiente del
     * vencimiento; si no, empieza el día del pago. Termina duracionDias − 1 días después del inicio.
     *
     * @return array{desde:Carbon,hasta:Carbon}
     */
    public function calcularPeriodo(Carbon $fechaPago, ?Carbon $vencimientoActual, int $duracionDias): array
    {
        $desde = $vencimientoActual && $vencimientoActual->gte($fechaPago)
            ? $vencimientoActual->copy()->addDay()
            : $fechaPago->copy();

        return ['desde' => $desde, 'hasta' => $desde->copy()->addDays($duracionDias - 1)];
    }
}
