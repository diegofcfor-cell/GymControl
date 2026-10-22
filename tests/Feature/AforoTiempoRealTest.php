<?php

namespace Tests\Feature;

use App\Events\AforoActualizado;
use App\Events\IngresoRegistrado;
use App\Listeners\TransmitirAforo;
use App\Livewire\AforoEnVivo;
use App\Models\AptoFisico;
use App\Models\Asistencia;
use App\Models\Membresia;
use App\Models\Socio;
use App\Models\User;
use App\Services\AccesoService;
use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

/** HU25: el aforo se transmite por WebSocket (Reverb) en cada ingreso o egreso. */
class AforoTiempoRealTest extends TestCase
{
    use RefreshDatabase;

    protected function socio(bool $apto = true): Socio
    {
        $user = User::factory()->create();
        $plan = Membresia::create(['nombre' => 'Plan'.uniqid(), 'precio' => 1000, 'duracion_dias' => 30, 'estado' => 'activa']);
        $socio = Socio::create([
            'membresia_id' => $plan->id, 'user_id' => $user->id, 'nombre' => 'Ana', 'apellido' => 'Test',
            'dni' => (string) random_int(20000000, 49999999), 'estado' => 'activo', 'fecha_vencimiento' => today()->addDays(10),
        ]);
        if ($apto) {
            AptoFisico::create(['socio_id' => $socio->id, 'user_id' => $user->id, 'fecha_emision' => today()->subMonth(),
                'fecha_vencimiento' => today()->addMonths(6), 'estado' => 'vigente']);
        }

        return $socio;
    }

    public function test_ingreso_y_egreso_transmiten_el_aforo_y_un_rechazo_no(): void
    {
        Event::fake([AforoActualizado::class]);
        $this->travelTo(today()->setTime(12, 0));
        $acceso = app(AccesoService::class);

        $acceso->procesar($this->socio(apto: false)->pin);
        Event::assertNotDispatched(AforoActualizado::class);

        $socio = $this->socio();
        $acceso->procesar($socio->pin);
        Event::assertDispatched(AforoActualizado::class, fn ($e) => $e->estado['presentes'] === 1);

        $acceso->procesar($socio->pin);
        Event::assertDispatched(AforoActualizado::class, fn ($e) => $e->estado['presentes'] === 0);
        Event::assertDispatchedTimes(AforoActualizado::class, 2);
    }

    public function test_el_evento_va_por_el_canal_publico_y_solo_lleva_numeros(): void
    {
        $evento = new AforoActualizado(['presentes' => 3, 'capacidad' => 50, 'porcentaje' => 6, 'nivel' => 'libre']);

        $this->assertSame('aforo', $evento->broadcastOn()->name);
        $this->assertSame('aforo.actualizado', $evento->broadcastAs());
        $this->assertSame(['presentes', 'capacidad', 'porcentaje', 'nivel'], array_keys($evento->broadcastWith()));
        Event::fake();
        Event::assertListening(IngresoRegistrado::class, TransmitirAforo::class);
    }

    public function test_si_el_servidor_de_websockets_falla_el_acceso_igual_se_registra(): void
    {
        Broadcast::extend('caido', fn () => new class extends Broadcaster
        {
            public function auth($request) {}

            public function validAuthenticationResponse($request, $result) {}

            public function broadcast(array $channels, $event, array $payload = []): void
            {
                throw new BroadcastException('Reverb no responde');
            }
        });
        config(['broadcasting.connections.caido' => ['driver' => 'caido'], 'broadcasting.default' => 'caido']);
        $socio = $this->socio();

        $r = app(AccesoService::class)->procesar($socio->pin);

        $this->assertSame('ingreso', $r['tipo']);
        $this->assertDatabaseCount('asistencias', 1);
    }

    public function test_el_componente_se_vuelve_a_dibujar_al_recibir_el_evento(): void
    {
        $componente = Livewire::test(AforoEnVivo::class)->assertSeeHtml('<span class="text-4xl font-bold">0</span>');

        Asistencia::create(['socio_id' => $this->socio()->id, 'fecha' => today(), 'hora_ingreso' => '00:00:00']);
        $componente->dispatch('echo:aforo,.aforo.actualizado')
            ->assertSeeHtml('<span class="text-4xl font-bold">1</span>');
    }

    public function test_la_pagina_solo_publica_la_clave_de_reverb_si_esta_activado(): void
    {
        $this->get('/')->assertOk()->assertDontSee('window.gymReverb', false);

        config(['broadcasting.default' => 'reverb', 'broadcasting.connections.reverb.key' => 'clave-publica',
            'broadcasting.connections.reverb.secret' => 'secreto']);
        $this->get('/')->assertSee('window.gymReverb', false)->assertSee('clave-publica')->assertDontSee('secreto');
    }
}
