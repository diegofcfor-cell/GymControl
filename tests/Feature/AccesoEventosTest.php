<?php

namespace Tests\Feature;

use App\Events\AccesoDenegado;
use App\Events\EgresoRegistrado;
use App\Events\IngresoRegistrado;
use App\Listeners\RegistrarAccesoEnLog;
use App\Models\AptoFisico;
use App\Models\Membresia;
use App\Models\Socio;
use App\Models\User;
use App\Services\AccesoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/** HU21: la pantalla de acceso anuncia ingresos, egresos y rechazos con eventos (patrón Observer). */
class AccesoEventosTest extends TestCase
{
    use RefreshDatabase;

    private const EVENTOS = [IngresoRegistrado::class, EgresoRegistrado::class, AccesoDenegado::class];

    protected function socio(bool $apto = true): Socio
    {
        $user = User::factory()->create();
        $plan = Membresia::create(['nombre' => 'Plan'.uniqid(), 'precio' => 1000, 'duracion_dias' => 30, 'estado' => 'activa']);
        $socio = Socio::create([
            'membresia_id' => $plan->id, 'user_id' => $user->id, 'nombre' => 'Ana', 'apellido' => 'Test',
            'dni' => '30111222', 'estado' => 'activo', 'fecha_vencimiento' => today()->addDays(10),
        ]);
        if ($apto) {
            AptoFisico::create(['socio_id' => $socio->id, 'user_id' => $user->id, 'fecha_emision' => today()->subMonth(),
                'fecha_vencimiento' => today()->addMonths(6), 'estado' => 'vigente']);
        }

        return $socio;
    }

    public function test_el_listener_queda_registrado_para_los_tres_eventos(): void
    {
        Event::fake();

        foreach (self::EVENTOS as $evento) {
            Event::assertListening($evento, RegistrarAccesoEnLog::class);
        }
    }

    public function test_ingreso_y_egreso_disparan_su_evento(): void
    {
        // Solo los eventos de acceso: un Event::fake() general apagaría Socio::booted(), que genera PIN y QR.
        Event::fake(self::EVENTOS);
        $this->travelTo(today()->setTime(12, 0));
        $socio = $this->socio();
        $acceso = app(AccesoService::class);

        $acceso->procesar($socio->pin);
        Event::assertDispatched(IngresoRegistrado::class, fn ($e) => $e->asistencia->socio_id === $socio->id && $e->asistencia->hora_salida === null);
        Event::assertNotDispatched(EgresoRegistrado::class);

        $this->travel(45)->minutes();
        $acceso->procesar($socio->pin);
        Event::assertDispatched(EgresoRegistrado::class, fn ($e) => $e->asistencia->tiempo_permanencia === '00:45:00');
    }

    public function test_un_rechazo_dispara_el_evento_con_sus_motivos_y_no_registra_ingreso(): void
    {
        Event::fake(self::EVENTOS);
        $socio = $this->socio(apto: false);

        app(AccesoService::class)->procesar($socio->pin);

        Event::assertDispatched(AccesoDenegado::class, fn ($e) => $e->socio->is($socio) && $e->motivos === ['Apto físico vencido o no presentado.']);
        Event::assertNotDispatched(IngresoRegistrado::class);
    }

    public function test_el_log_guarda_el_id_y_los_motivos_pero_no_datos_personales(): void
    {
        $socio = $this->socio(apto: false);
        Log::shouldReceive('info')->once()->withArgs(function (string $mensaje, array $contexto) use ($socio) {
            return $mensaje === 'Acceso: denegado'
                && $contexto === ['socio_id' => $socio->id, 'motivos' => ['Apto físico vencido o no presentado.']]
                && ! str_contains(json_encode($contexto), $socio->dni);
        });

        app(AccesoService::class)->procesar($socio->pin);
    }
}
