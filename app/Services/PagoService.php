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

            $desde = $fechaPago->copy();
            if ($socio->fecha_vencimiento && $socio->fecha_vencimiento->gte($fechaPago)) {
                $desde = $socio->fecha_vencimiento->copy()->addDay();
            }
            $hasta = $desde->copy()->addDays($membresia->duracion_dias - 1);

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
}
