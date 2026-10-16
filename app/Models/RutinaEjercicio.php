<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RutinaEjercicio extends Model
{
    protected $table = 'rutina_ejercicios';

    protected $fillable = ['rutina_id', 'dia', 'ejercicio', 'series', 'repeticiones', 'descanso', 'notas', 'orden'];

    protected function casts(): array
    {
        return ['series' => 'integer', 'orden' => 'integer'];
    }
}
