<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudRutina extends Model
{
    protected $table = 'solicitudes_rutina';

    public const OBJETIVOS = [
        'Ganar masa muscular', 'Bajar de peso', 'Tonificar', 'Mejorar resistencia', 'Salud general', 'Rehabilitación / volver a entrenar',
    ];

    public const NIVELES = ['principiante' => 'Principiante', 'intermedio' => 'Intermedio', 'avanzado' => 'Avanzado'];

    protected $fillable = ['socio_id', 'objetivo', 'dias_por_semana', 'nivel', 'comentarios', 'estado', 'rutina_id'];

    protected function casts(): array
    {
        return ['dias_por_semana' => 'integer'];
    }

    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class)->withTrashed();
    }

    public function rutina(): BelongsTo
    {
        return $this->belongsTo(Rutina::class);
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }
}
