<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'socio_id', 'membresia_id', 'user_id', 'importe', 'fecha_pago',
        'periodo_desde', 'periodo_hasta', 'metodo_pago',
    ];

    protected function casts(): array
    {
        return [
            'importe' => 'decimal:2',
            'fecha_pago' => 'date',
            'periodo_desde' => 'date',
            'periodo_hasta' => 'date',
        ];
    }

    public function socio(): BelongsTo
    {
        return $this->belongsTo(Socio::class)->withTrashed();
    }

    public function membresia(): BelongsTo
    {
        return $this->belongsTo(Membresia::class);
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
