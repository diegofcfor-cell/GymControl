<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Parámetros del gimnasio editables por el administrador (clave → valor). */
class Configuracion extends Model
{
    protected $table = 'configuraciones';

    protected $fillable = ['clave', 'valor'];

    /** Datos que se muestran en la página web pública (y que usa el chatbot). */
    public const DATOS_WEB = ['descripcion', 'servicios', 'horarios', 'direccion', 'telefono', 'email', 'instagram'];

    /** Ingreso con código QR (HU33): apagado por defecto; el gimnasio usa solo PIN y puede activarlo en Configuración. */
    public const ACCESO_QR = 'acceso_qr';

    public static function accesoQrHabilitado(): bool
    {
        return self::obtener(self::ACCESO_QR, '0') === '1';
    }

    public static function obtener(string $clave, ?string $porDefecto = null): ?string
    {
        return self::where('clave', $clave)->value('valor') ?? $porDefecto;
    }

    /** @return array<string,?string> clave => valor (null si no se cargó) */
    public static function varios(array $claves): array
    {
        $valores = self::whereIn('clave', $claves)->pluck('valor', 'clave');

        return collect($claves)->mapWithKeys(fn ($c) => [$c => $valores[$c] ?? null])->all();
    }

    public static function guardar(string $clave, ?string $valor): void
    {
        self::updateOrCreate(['clave' => $clave], ['valor' => $valor]);
    }
}
