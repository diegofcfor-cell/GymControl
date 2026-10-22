<?php

namespace App\Repositories;

use App\Models\Asistencia;
use App\Models\Socio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Consultas de asistencias que comparten varios módulos (aforo, acceso, dashboard y portal).
 *
 * Regla que centraliza: un socio está "presente" si tiene una asistencia de hoy sin egreso.
 * Si esa regla cambia, se cambia solo acá.
 */
class AsistenciaRepository
{
    public function cantidadPresentes(): int
    {
        return $this->presentes(Asistencia::query())->count();
    }

    /** Socios en sala, del que entró primero al último. */
    public function presentesHoy(): Collection
    {
        return $this->presentes(Asistencia::with('socio'))->orderBy('hora_ingreso')->get();
    }

    /** La asistencia abierta de hoy del socio, o null si no está en el gimnasio. */
    public function abiertaDeHoy(Socio $socio): ?Asistencia
    {
        return $this->presentes($socio->asistencias())->latest('id')->first();
    }

    public function estaDentro(Socio $socio): bool
    {
        return $this->presentes($socio->asistencias())->exists();
    }

    /** Minutos que el socio ya entrenó hoy, sumando solo las asistencias cerradas. */
    public function minutosHoy(Socio $socio): int
    {
        return (int) $socio->asistencias()
            ->whereDate('fecha', today())
            ->whereNotNull('tiempo_permanencia')
            ->get()
            ->sum(fn (Asistencia $a) => $a->minutosPermanencia() ?? 0);
    }

    private function presentes(Builder|HasMany $consulta): Builder|HasMany
    {
        return $consulta->abiertas()->whereDate('fecha', today());
    }
}
