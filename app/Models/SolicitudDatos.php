<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Pedido de rectificación o supresión de datos personales que el socio hace desde su portal. */
class SolicitudDatos extends Model
{
    protected $table = 'solicitudes_datos';

    public const TIPOS = ['rectificacion' => 'Rectificación', 'supresion' => 'Supresión'];

    /** Plazo para responder según el art. 16 de la Ley 25.326. */
    public const PLAZO_DIAS_HABILES = 5;

    protected $fillable = ['socio_id', 'tipo', 'detalle', 'estado', 'respuesta', 'user_id', 'resuelta_at'];

    protected function casts(): array
    {
        return ['resuelta_at' => 'datetime'];
    }

    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class)->withTrashed();
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Fecha límite para responder (cuenta lunes a viernes; no descuenta feriados). */
    public function plazo(): Carbon
    {
        return $this->created_at->copy()->startOfDay()->addWeekdays(self::PLAZO_DIAS_HABILES);
    }

    public function estaVencida(): bool
    {
        return $this->estado === 'pendiente' && today()->gt($this->plazo());
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }
}
