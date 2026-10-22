<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class Socio extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'socios';

    protected $fillable = [
        'membresia_id', 'user_id', 'nombre', 'apellido', 'dni', 'telefono', 'email',
        'estado', 'fecha_alta', 'fecha_vencimiento', 'consentimiento_datos_at',
    ];

    protected function casts(): array
    {
        return [
            'fecha_alta' => 'date',
            'fecha_vencimiento' => 'date',
            'consentimiento_datos_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Socio $socio) {
            $socio->pin ??= self::generarPin();
            $socio->qr_codigo ??= (string) Str::uuid();
            $socio->fecha_alta ??= today();
        });
    }

    /** Aceptó el aviso de privacidad: requisito para cargar sus datos de salud (apto físico). */
    public function dioConsentimiento(): bool
    {
        return $this->consentimiento_datos_at !== null;
    }

    /** PIN numérico de 6 dígitos, único. */
    public static function generarPin(): string
    {
        do {
            $pin = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::withTrashed()->where('pin', $pin)->exists());

        return $pin;
    }

    public function membresia(): BelongsTo
    {
        return $this->belongsTo(Membresia::class);
    }

    public function operador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function aptos(): HasMany
    {
        return $this->hasMany(AptoFisico::class);
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class);
    }

    public function rutinas(): HasMany
    {
        return $this->hasMany(Rutina::class);
    }

    public function rutinaActiva(): ?Rutina
    {
        return $this->rutinas()->where('activa', true)->latest('id')->first();
    }

    public function solicitudesRutina(): HasMany
    {
        return $this->hasMany(SolicitudRutina::class);
    }

    public function getNombreCompletoAttribute(): string
    {
        return $this->apellido.', '.$this->nombre;
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }

    public function cuotaAlDia(): bool
    {
        return $this->fecha_vencimiento !== null && $this->fecha_vencimiento->gte(today());
    }

    public function aptoVigente(): bool
    {
        return $this->aptos()->whereDate('fecha_vencimiento', '>=', today())->exists();
    }

    public function ultimoApto(): ?AptoFisico
    {
        return $this->aptos()->orderByDesc('fecha_vencimiento')->first();
    }

    public function scopeBuscar($query, ?string $texto)
    {
        $texto = trim((string) $texto);
        if ($texto === '') {
            return $query;
        }

        return $query->where(function ($q) use ($texto) {
            $q->where('nombre', 'like', "%{$texto}%")
                ->orWhere('apellido', 'like', "%{$texto}%")
                ->orWhere('dni', 'like', "%{$texto}%")
                ->orWhere('pin', $texto);
        });
    }

    public function scopeConCuotaAlDia($query)
    {
        return $query->whereDate('fecha_vencimiento', '>=', today());
    }

    public function scopeConCuotaVencida($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('fecha_vencimiento')->orWhereDate('fecha_vencimiento', '<', today());
        });
    }
}
