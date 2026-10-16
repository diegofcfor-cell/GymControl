<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreguntaFrecuente extends Model
{
    protected $table = 'preguntas_frecuentes';

    protected $fillable = ['pregunta', 'respuesta', 'palabras_clave', 'orden', 'activa'];

    protected function casts(): array
    {
        return ['activa' => 'boolean', 'orden' => 'integer'];
    }

    public function scopeActivas($query)
    {
        return $query->where('activa', true)->orderBy('orden')->orderBy('id');
    }
}
