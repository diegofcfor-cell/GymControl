<?php

namespace Tests\Feature;

use App\Models\AptoFisico;
use App\Models\Asistencia;
use App\Models\Membresia;
use App\Models\Socio;
use App\Models\User;
use App\Services\AccesoService;
use App\Services\ReglasIngreso\CuotaVigente;
use App\Services\ReglasIngreso\ReglaIngreso;
use App\Services\ReglasIngreso\SocioActivo;
use App\Services\ReglasIngreso\TopeHorasDiarias;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** HU22: cada condición de ingreso es una estrategia intercambiable (patrón Strategy). */
class ReglasIngresoTest extends TestCase
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

    public function test_cada_regla_devuelve_su_motivo_solo_si_no_se_cumple(): void
    {
        $alDia = $this->socio();
        $vencido = $this->socio(['estado' => 'inactivo', 'fecha_vencimiento' => today()->subDay()]);

        $this->assertNull((new SocioActivo)->motivoDeRechazo($alDia));
        $this->assertNull((new CuotaVigente)->motivoDeRechazo($alDia));
        $this->assertSame('Socio dado de baja o inactivo.', (new SocioActivo)->motivoDeRechazo($vencido));
        $this->assertSame('Cuota vencida: regularice su pago en recepción.', (new CuotaVigente)->motivoDeRechazo($vencido));
    }

    public function test_el_tope_diario_cuenta_solo_las_asistencias_cerradas_de_hoy(): void
    {
        $socio = $this->socio(horas: 2);
        $regla = app(TopeHorasDiarias::class);
        Asistencia::create(['socio_id' => $socio->id, 'fecha' => today()->subDay(), 'hora_ingreso' => '08:00:00',
            'hora_salida' => '11:00:00', 'tiempo_permanencia' => '03:00:00']);
        $this->assertNull($regla->motivoDeRechazo($socio));

        Asistencia::create(['socio_id' => $socio->id, 'fecha' => today(), 'hora_ingreso' => '08:00:00',
            'hora_salida' => '10:00:00', 'tiempo_permanencia' => '02:00:00']);
        $this->assertSame('Alcanzó el tope de horas diarias de su membresía.', $regla->motivoDeRechazo($socio));
    }

    public function test_el_rechazo_muestra_todos_los_motivos_en_el_orden_configurado(): void
    {
        $socio = $this->socio(['estado' => 'inactivo', 'fecha_vencimiento' => today()->subDay()], apto: false);

        $r = app(AccesoService::class)->procesar($socio->pin);

        $this->assertSame('denegado', $r['tipo']);
        $this->assertSame([
            'Socio dado de baja o inactivo.',
            'Cuota vencida: regularice su pago en recepción.',
            'Apto físico vencido o no presentado.',
        ], $r['motivos']);
    }

    public function test_una_regla_nueva_se_suma_sin_modificar_el_servicio(): void
    {
        // OCP: el servicio no cambia; solo cambia la lista de estrategias que recibe.
        $this->app->when(AccesoService::class)->needs(ReglaIngreso::class)->give(fn () => [
            new SocioActivo,
            new class implements ReglaIngreso
            {
                public function motivoDeRechazo(Socio $socio): ?string
                {
                    return 'El gimnasio está cerrado por feriado.';
                }
            },
        ]);
        $socio = $this->socio();

        $r = app(AccesoService::class)->procesar($socio->pin);

        $this->assertSame(['El gimnasio está cerrado por feriado.'], $r['motivos']);
        $this->assertDatabaseCount('asistencias', 0);
    }
}
