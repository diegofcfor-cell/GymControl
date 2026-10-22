<?php

namespace Tests\Feature;

use App\Models\Asistencia;
use App\Models\Membresia;
use App\Models\Pago;
use App\Models\Socio;
use App\Models\User;
use App\Repositories\AsistenciaRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** HU29: panel de recepción con buscador, socios en sala y últimos accesos, sin datos económicos. */
class RecepcionTest extends TestCase
{
    use RefreshDatabase;

    protected function socio(string $nombre): Socio
    {
        $plan = Membresia::create(['nombre' => 'Plan'.uniqid(), 'precio' => 1000, 'duracion_dias' => 30, 'estado' => 'activa']);

        return Socio::create([
            'membresia_id' => $plan->id, 'user_id' => User::factory()->create()->id, 'nombre' => $nombre, 'apellido' => 'Test',
            'dni' => (string) random_int(20000000, 49999999), 'estado' => 'activo',
        ]);
    }

    public function test_el_recepcionista_entra_al_panel_de_recepcion(): void
    {
        $this->actingAs(User::factory()->create())->get('/panel')->assertRedirect('/recepcion');
    }

    public function test_muestra_socios_en_sala_y_accesos_sin_datos_economicos(): void
    {
        $enSala = $this->socio('Ana');
        $salio = $this->socio('Bruno');
        Asistencia::create(['socio_id' => $enSala->id, 'fecha' => today(), 'hora_ingreso' => '09:00:00']);
        Asistencia::create(['socio_id' => $salio->id, 'fecha' => today(), 'hora_ingreso' => '08:00:00', 'hora_salida' => '09:30:00', 'tiempo_permanencia' => '01:30:00']);
        Pago::create([
            'socio_id' => $salio->id, 'membresia_id' => $salio->membresia_id, 'user_id' => $salio->user_id, 'importe' => 98765,
            'fecha_pago' => today(), 'periodo_desde' => today(), 'periodo_hasta' => today()->addDays(29), 'metodo_pago' => 'efectivo',
        ]);

        $this->actingAs(User::factory()->create())->get('/recepcion')
            ->assertOk()
            ->assertSee('Socios en sala')
            ->assertSee('Test, Ana')
            ->assertSee('Test, Bruno')
            ->assertSee('en sala')
            ->assertSee('09:30')
            ->assertSee(route('socios.index'), false)
            ->assertDontSee('Ingresos')
            ->assertDontSee('98.765');
    }

    public function test_el_profesor_no_accede_al_panel_de_recepcion(): void
    {
        $profesor = User::factory()->create(['rol' => User::ROL_PROFESOR]);

        $this->actingAs($profesor)->get('/recepcion')->assertForbidden();
    }

    public function test_ultimos_accesos_ordena_por_el_movimiento_mas_reciente(): void
    {
        $entroTarde = $this->socio('Entró');
        $salioRecien = $this->socio('Salió');
        $ayer = $this->socio('Ayer');
        Asistencia::create(['socio_id' => $entroTarde->id, 'fecha' => today(), 'hora_ingreso' => '10:00:00']);
        Asistencia::create(['socio_id' => $salioRecien->id, 'fecha' => today(), 'hora_ingreso' => '08:00:00', 'hora_salida' => '10:30:00']);
        Asistencia::create(['socio_id' => $ayer->id, 'fecha' => today()->subDay(), 'hora_ingreso' => '11:00:00']);

        $accesos = (new AsistenciaRepository)->ultimosAccesosHoy();

        $this->assertSame(['Salió', 'Entró'], $accesos->pluck('socio.nombre')->all());
        $this->assertCount(1, (new AsistenciaRepository)->ultimosAccesosHoy(1));
    }

    public function test_el_dashboard_tambien_muestra_los_ultimos_accesos(): void
    {
        Asistencia::create(['socio_id' => $this->socio('Carla')->id, 'fecha' => today(), 'hora_ingreso' => '07:15:00']);

        $this->actingAs(User::factory()->administrador()->create())->get('/dashboard')
            ->assertOk()
            ->assertSee('Últimos accesos de hoy')
            ->assertSee('Test, Carla');
    }
}
