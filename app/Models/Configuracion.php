<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Parámetros del gimnasio editables por el administrador (clave → valor). */
class Configuracion extends Model
{
    protected $table = 'configuraciones';

    protected $fillable = ['clave', 'valor'];

    public static function obtener(string $clave, ?string $porDefecto = null): ?string
    {
        return self::where('clave', $clave)->value('valor') ?? $porDefecto;
    }

    public static function guardar(string $clave, ?string $valor): void
    {
        self::updateOrCreate(['clave' => $clave], ['valor' => $valor]);
    }
}
