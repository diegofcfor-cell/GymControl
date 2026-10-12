<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asistencia extends Model
{
    protected $table = 'asistencias';

    protected $fillable = ['socio_id', 'fecha', 'hora_ingreso', 'hora_salida', 'tiempo_permanencia'];

    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }

    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class)->withTrashed();
    }

    public function momentoIngreso(): Carbon
    {
        return Carbon::parse($this->fecha->format('Y-m-d').' '.$this->hora_ingreso);
    }

    /** Minutos de permanencia (NULL si aún no egresó). */
    public function minutosPermanencia(): ?int
    {
        if ($this->tiempo_permanencia === null) {
            return null;
        }
        [$h, $m] = array_map('intval', explode(':', $this->tiempo_permanencia));

        return $h * 60 + $m;
    }

    public function permanenciaTexto(): string
    {
        $min = $this->minutosPermanencia();
        if ($min === null) {
            return '—';
        }

        return intdiv($min, 60).' h '.str_pad((string) ($min % 60), 2, '0', STR_PAD_LEFT).' min';
    }

    public function scopeAbiertas($query)
    {
        return $query->whereNull('hora_salida');
    }
}
