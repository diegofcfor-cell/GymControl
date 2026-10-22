<?php

namespace App\Services;

use App\Models\Socio;
use App\Models\SolicitudDatos;
use App\Models\User;

/**
 * Derechos de rectificación y supresión del socio (Ley 25.326, art. 16).
 *
 * Reglas:
 * - El socio puede tener un solo pedido pendiente de cada tipo.
 * - El administrador responde dentro de los 5 días hábiles; la respuesta queda registrada con quién y cuándo.
 * - Resolver no borra datos: el administrador hace la corrección o la baja desde la ficha del socio.
 */
class SolicitudDatosService
{
    /** Registra el pedido. Devuelve null si ya hay uno pendiente del mismo tipo. */
    public function registrar(Socio $socio, string $tipo, string $detalle): ?SolicitudDatos
    {
        if ($socio->solicitudesDatos()->pendientes()->where('tipo', $tipo)->exists()) {
            return null;
        }

        return $socio->solicitudesDatos()->create(['tipo' => $tipo, 'detalle' => $detalle, 'estado' => 'pendiente']);
    }

    public function resolver(SolicitudDatos $solicitud, User $administrador, string $respuesta): SolicitudDatos
    {
        $solicitud->update([
            'estado' => 'resuelta',
            'respuesta' => $respuesta,
            'user_id' => $administrador->id,
            'resuelta_at' => now(),
        ]);

        return $solicitud;
    }
}
