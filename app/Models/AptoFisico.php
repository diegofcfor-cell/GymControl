<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AptoFisico extends Model
{
    protected $table = 'aptos_fisicos';

    protected $fillable = ['socio_id', 'user_id', 'fecha_emision', 'fecha_vencimiento', 'estado', 'archivo'];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_vencimiento' => 'date',
        ];
    }

    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class)->withTrashed();
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function estaVigente(): bool
    {
        return $this->fecha_vencimiento->gte(today());
    }
}
