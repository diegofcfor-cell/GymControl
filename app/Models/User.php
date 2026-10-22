<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROL_ADMIN = 'administrador';

    public const ROL_RECEPCION = 'recepcionista';

    public const ROL_PROFESOR = 'profesor';

    public const ROLES = [self::ROL_ADMIN, self::ROL_RECEPCION, self::ROL_PROFESOR];

    protected $fillable = ['name', 'email', 'password', 'rol', 'activo'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function esAdministrador(): bool
    {
        return $this->rol === self::ROL_ADMIN;
    }

    public function esRecepcionista(): bool
    {
        return $this->rol === self::ROL_RECEPCION;
    }

    public function esProfesor(): bool
    {
        return $this->rol === self::ROL_PROFESOR;
    }

    /** Pantalla de inicio de cada rol al ingresar al panel. */
    public function rutaInicio(): string
    {
        return match ($this->rol) {
            self::ROL_ADMIN => 'dashboard',
            self::ROL_PROFESOR => 'rutinas.index',
            default => 'recepcion',
        };
    }
}
