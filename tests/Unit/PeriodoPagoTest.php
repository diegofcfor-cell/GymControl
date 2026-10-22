<?php

namespace Tests\Unit;

use App\Services\PagoService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

/** Cálculo del período que cubre un pago (regla de negocio de PagoService, sin base de datos). */
class PeriodoPagoTest extends TestCase
{
    protected function periodo(string $pago, ?string $vencimiento, int $dias = 30): array
    {
        $p = (new PagoService)->calcularPeriodo(Carbon::parse($pago), $vencimiento ? Carbon::parse($vencimiento) : null, $dias);

        return [$p['desde']->toDateString(), $p['hasta']->toDateString()];
    }

    public function test_el_primer_pago_empieza_el_dia_del_pago(): void
    {
        $this->assertSame(['2026-11-06', '2026-12-05'], $this->periodo('2026-11-06', null));
    }

    public function test_con_la_cuota_vigente_empieza_al_dia_siguiente_del_vencimiento(): void
    {
        $this->assertSame(['2026-11-16', '2026-12-15'], $this->periodo('2026-11-06', '2026-11-15'));
    }

    public function test_si_vence_el_mismo_dia_del_pago_tambien_se_encadena(): void
    {
        $this->assertSame(['2026-11-07', '2026-12-06'], $this->periodo('2026-11-06', '2026-11-06'));
    }

    public function test_con_la_cuota_vencida_empieza_el_dia_del_pago(): void
    {
        $this->assertSame(['2026-11-06', '2026-12-05'], $this->periodo('2026-11-06', '2026-11-01'));
    }

    public function test_el_periodo_dura_exactamente_la_duracion_del_plan(): void
    {
        $this->assertSame(['2026-11-06', '2026-11-06'], $this->periodo('2026-11-06', null, 1));
        $this->assertSame(['2026-11-06', '2027-11-05'], $this->periodo('2026-11-06', null, 365));
    }
}
