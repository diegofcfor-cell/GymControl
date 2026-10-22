<?php

namespace Tests\Unit;

use App\Models\Asistencia;
use App\Repositories\AsistenciaRepository;
use App\Services\AccesoService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

/** Cálculo y presentación del tiempo de permanencia en el gimnasio. */
class PermanenciaTest extends TestCase
{
    protected function permanencia(string $ingreso, string $salida): string
    {
        return (new AccesoService(new AsistenciaRepository))->calcularPermanencia(Carbon::parse($ingreso), Carbon::parse($salida));
    }

    public function test_calcula_la_diferencia_entre_ingreso_y_egreso(): void
    {
        $this->assertSame('01:05:30', $this->permanencia('2026-11-06 18:00:00', '2026-11-06 19:05:30'));
    }

    public function test_una_salida_anterior_al_ingreso_no_da_tiempo_negativo(): void
    {
        $this->assertSame('00:00:00', $this->permanencia('2026-11-06 18:00:00', '2026-11-06 17:59:00'));
    }

    public function test_nunca_supera_un_dia(): void
    {
        $this->assertSame('23:59:59', $this->permanencia('2026-11-06 08:00:00', '2026-11-08 08:00:00'));
    }

    public function test_minutos_y_texto_de_la_asistencia(): void
    {
        $asistencia = new Asistencia(['tiempo_permanencia' => '01:05:30']);
        $this->assertSame(65, $asistencia->minutosPermanencia());
        $this->assertSame('1 h 05 min', $asistencia->permanenciaTexto());

        $enSala = new Asistencia(['tiempo_permanencia' => null]);
        $this->assertNull($enSala->minutosPermanencia());
        $this->assertSame('—', $enSala->permanenciaTexto());
    }
}
