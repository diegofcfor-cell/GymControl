<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Membresia extends Model
{
    protected $table = 'membresias';

    protected $fillable = ['nombre', 'precio', 'duracion_dias', 'horas_diarias_max', 'estado'];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'duracion_dias' => 'integer',
            'horas_diarias_max' => 'integer',
        ];
    }

    public function socios(): HasMany
    {
        return $this->hasMany(Socio::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }

    public function esPaseLibre(): bool
    {
        return $this->horas_diarias_max === null;
    }
}
