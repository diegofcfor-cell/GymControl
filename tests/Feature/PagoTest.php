<?php

namespace Tests\Feature;

use App\Models\Membresia;
use App\Models\Socio;
use App\Models\User;
use App\Services\PagoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagoTest extends TestCase
{
    use RefreshDatabase;

    protected function datos(?string $vence = null): array
    {
        $user = User::factory()->create();
        $plan = Membresia::create(['nombre' => 'Media', 'precio' => 35000, 'duracion_dias' => 30, 'horas_diarias_max' => 4, 'estado' => 'activa']);
        $socio = Socio::create(['membresia_id' => $plan->id, 'user_id' => $user->id, 'nombre' => 'Pedro', 'apellido' => 'Paz',
            'dni' => '30123456', 'estado' => 'activo', 'fecha_vencimiento' => $vence]);

        return [$socio, $plan, $user];
    }

    public function test_primer_pago_calcula_vencimiento_desde_hoy(): void
    {
        [$socio, $plan, $user] = $this->datos();
        $pago = app(PagoService::class)->registrar($socio, $plan, 'efectivo', $user);

        $this->assertSame(today()->addDays(29)->toDateString(), $socio->fresh()->fecha_vencimiento->toDateString());
        $this->assertSame('35000.00', (string) $pago->importe);
    }

    public function test_pago_anticipado_extiende_desde_el_vencimiento_actual(): void
    {
        [$socio, $plan, $user] = $this->datos(today()->addDays(5)->toDateString());
        app(PagoService::class)->registrar($socio, $plan, 'efectivo', $user);

        $this->assertSame(today()->addDays(5)->addDays(30)->toDateString(), $socio->fresh()->fecha_vencimiento->toDateString());
    }

    public function test_pago_con_cuota_vencida_comienza_hoy(): void
    {
        [$socio, $plan, $user] = $this->datos(today()->subDays(20)->toDateString());
        app(PagoService::class)->registrar($socio, $plan, 'transferencia', $user);

        $this->assertSame(today()->addDays(29)->toDateString(), $socio->fresh()->fecha_vencimiento->toDateString());
    }
}
