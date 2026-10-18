<?php

namespace Tests\Feature;

use App\Models\AptoFisico;
use App\Models\Asistencia;
use App\Models\Membresia;
use App\Models\Socio;
use App\Models\User;
use App\Services\AccesoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AccesoTest extends TestCase
{
    use RefreshDatabase;

    protected function socio(array $override = [], bool $apto = true, ?int $horas = null): Socio
    {
        $user = User::factory()->create();
        $plan = Membresia::create(['nombre' => 'Plan'.uniqid(), 'precio' => 1000, 'duracion_dias' => 30, 'horas_diarias_max' => $horas, 'estado' => 'activa']);
        $socio = Socio::create($override + [
            'membresia_id' => $plan->id, 'user_id' => $user->id, 'nombre' => 'Ana', 'apellido' => 'Test',
            'dni' => (string) random_int(20000000, 49999999), 'estado' => 'activo', 'fecha_vencimiento' => today()->addDays(10),
        ]);
        if ($apto) {
            AptoFisico::create(['socio_id' => $socio->id, 'user_id' => $user->id, 'fecha_emision' => today()->subMonth(),
                'fecha_vencimiento' => today()->addMonths(6), 'estado' => 'vigente']);
        }

        return $socio;
    }

    public function test_el_socio_recibe_pin_de_6_digitos_y_qr_unicos(): void
    {
        $s = $this->socio();
        $this->assertMatchesRegularExpression('/^\d{6}$/', $s->pin);
        $this->assertNotEmpty($s->qr_codigo);
    }

    public function test_ingreso_y_egreso_por_pin_calculan_permanencia(): void
    {
        // Mediodía fijo: restar 90 minutos cerca de la medianoche cruzaría al día anterior.
        $this->travelTo(today()->setTime(12, 0));
        $s = $this->socio();
        $svc = app(AccesoService::class);

        $r1 = $svc->procesar($s->pin);
        $this->assertTrue($r1['ok']);
        $this->assertSame('ingreso', $r1['tipo']);
        $this->assertDatabaseCount('asistencias', 1);

        // simular 90 minutos dentro del gimnasio
        $a = Asistencia::first();
        $a->update(['hora_ingreso' => now()->subMinutes(90)->format('H:i:s')]);

        $r2 = $svc->procesar($s->qr_codigo); // egreso con QR
        $this->assertTrue($r2['ok']);
        $this->assertSame('egreso', $r2['tipo']);
        $a->refresh();
        $this->assertNotNull($a->hora_salida);
        $this->assertSame(90, $a->minutosPermanencia());
    }

    public function test_cuota_vencida_deniega_ingreso(): void
    {
        $s = $this->socio(['fecha_vencimiento' => today()->subDay()]);
        $r = app(AccesoService::class)->procesar($s->pin);
        $this->assertFalse($r['ok']);
        $this->assertSame('denegado', $r['tipo']);
        $this->assertDatabaseCount('asistencias', 0);
    }

    public function test_sin_apto_fisico_deniega_ingreso(): void
    {
        $s = $this->socio([], false);
        $r = app(AccesoService::class)->procesar($s->pin);
        $this->assertFalse($r['ok']);
    }

    public function test_el_egreso_se_permite_aunque_la_cuota_haya_vencido(): void
    {
        $s = $this->socio();
        $svc = app(AccesoService::class);
        $svc->procesar($s->pin);
        $s->update(['fecha_vencimiento' => today()->subDay()]);

        $r = $svc->procesar($s->pin);
        $this->assertTrue($r['ok']);
        $this->assertSame('egreso', $r['tipo']);
    }

    public function test_tope_de_horas_diarias_bloquea_nuevo_ingreso(): void
    {
        $s = $this->socio([], true, 2);
        Asistencia::create(['socio_id' => $s->id, 'fecha' => today()->toDateString(), 'hora_ingreso' => '08:00:00',
            'hora_salida' => '10:00:00', 'tiempo_permanencia' => '02:00:00']);

        $r = app(AccesoService::class)->procesar($s->pin);
        $this->assertFalse($r['ok']);
    }

    public function test_pase_libre_no_tiene_tope(): void
    {
        $s = $this->socio([], true, null);
        Asistencia::create(['socio_id' => $s->id, 'fecha' => today()->toDateString(), 'hora_ingreso' => '06:00:00',
            'hora_salida' => '14:00:00', 'tiempo_permanencia' => '08:00:00']);

        $this->assertTrue(app(AccesoService::class)->procesar($s->pin)['ok']);
    }

    public function test_codigo_inexistente_y_limite_de_intentos(): void
    {
        RateLimiter::clear('acceso-fallido:test');
        $svc = app(AccesoService::class);
        for ($i = 0; $i < AccesoService::MAX_INTENTOS_FALLIDOS; $i++) {
            $this->assertSame('desconocido', $svc->procesar('000000', 'test')['tipo']);
        }
        $this->assertSame('bloqueado', $svc->procesar('000000', 'test')['tipo']);
    }

    public function test_socio_dado_de_baja_no_ingresa(): void
    {
        $s = $this->socio();
        $s->delete();
        $this->assertSame('desconocido', app(AccesoService::class)->procesar($s->pin)['tipo']);
    }
}
