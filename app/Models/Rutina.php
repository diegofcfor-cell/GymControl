<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rutina extends Model
{
    protected $table = 'rutinas';

    protected $fillable = ['socio_id', 'user_id', 'nombre', 'objetivo', 'observaciones', 'activa'];

    protected function casts(): array
    {
        return ['activa' => 'boolean'];
    }

    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class)->withTrashed();
    }

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ejercicios(): HasMany
    {
        return $this->hasMany(RutinaEjercicio::class)->orderBy('orden');
    }

    /** Ejercicios agrupados por día, en el orden en que se cargaron. */
    public function porDia()
    {
        return $this->ejercicios->groupBy('dia');
    }
}
