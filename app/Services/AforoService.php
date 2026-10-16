<?php

namespace App\Services;

use App\Models\Asistencia;
use App\Models\Configuracion;
use Carbon\Carbon;

/**
 * Aforo del gimnasio:
 *  - Presentes = asistencias de hoy sin egreso.
 *  - Nivel según % de la capacidad: libre (< 50 %), moderado (< 80 %), lleno (>= 80 %).
 *  - Horas pico = promedio de personas presentes por hora para un día de la semana,
 *    calculado con las asistencias de las últimas semanas.
 */
class AforoService
{
    public const CAPACIDAD_POR_DEFECTO = 50;

    public const SEMANAS_HISTORIAL = 4;

    /** Franja horaria que se muestra en el gráfico de horas pico. */
    public const HORA_APERTURA = 6;

    public const HORA_CIERRE = 23;

    /** Minutos que se asumen para una asistencia sin egreso registrado. */
    public const PERMANENCIA_ESTIMADA = 60;

    public function capacidad(): int
    {
        return max(1, (int) Configuracion::obtener('capacidad_maxima', (string) self::CAPACIDAD_POR_DEFECTO));
    }

    public function presentes(): int
    {
        return Asistencia::abiertas()->whereDate('fecha', today())->count();
    }

    /** @return array{presentes:int,capacidad:int,porcentaje:int,nivel:string} */
    public function estado(): array
    {
        $presentes = $this->presentes();
        $capacidad = $this->capacidad();
        $porcentaje = (int) round($presentes * 100 / $capacidad);

        return [
            'presentes' => $presentes,
            'capacidad' => $capacidad,
            'porcentaje' => $porcentaje,
            'nivel' => $this->nivel($porcentaje),
        ];
    }

    public function nivel(int $porcentaje): string
    {
        return match (true) {
            $porcentaje >= 80 => 'lleno',
            $porcentaje >= 50 => 'moderado',
            default => 'libre',
        };
    }

    /**
     * Promedio de personas presentes en cada hora para el día de la semana dado
     * (0 = domingo … 6 = sábado), sin contar el día de hoy.
     *
     * @return array<int,float> hora => promedio
     */
    public function horasPico(int $diaSemana): array
    {
        $horas = array_fill_keys(range(self::HORA_APERTURA, self::HORA_CIERRE), 0);

        $desde = today()->subWeeks(self::SEMANAS_HISTORIAL);
        $asistencias = Asistencia::whereDate('fecha', '>=', $desde)
            ->whereDate('fecha', '<', today())
            ->get(['fecha', 'hora_ingreso', 'hora_salida'])
            ->filter(fn ($a) => $a->fecha->dayOfWeek === $diaSemana);

        foreach ($asistencias as $a) {
            $ingreso = $a->momentoIngreso();
            $salida = $a->hora_salida
                ? Carbon::parse($a->fecha->format('Y-m-d').' '.$a->hora_salida)
                : $ingreso->copy()->addMinutes(self::PERMANENCIA_ESTIMADA);

            // La persona cuenta en cada hora en la que estuvo dentro aunque sea un rato.
            foreach (array_keys($horas) as $h) {
                $inicio = $a->fecha->copy()->setTime($h, 0);
                if ($ingreso->lt($inicio->copy()->addHour()) && $salida->gt($inicio)) {
                    $horas[$h]++;
                }
            }
        }

        // Cantidad de días de ese tipo dentro del período (para promediar).
        $dias = 0;
        for ($d = $desde->copy(); $d->lt(today()); $d->addDay()) {
            if ($d->dayOfWeek === $diaSemana) {
                $dias++;
            }
        }

        return array_map(fn ($n) => round($n / max(1, $dias), 1), $horas);
    }
}
