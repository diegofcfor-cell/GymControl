<?php

namespace Tests\Feature;

use App\Models\AptoFisico;
use App\Models\Asistencia;
use App\Models\Membresia;
use App\Models\Socio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalSocioTest extends TestCase
{
    use RefreshDatabase;

    protected function socio(array $override = []): Socio
    {
        $user = User::factory()->create();
        $plan = Membresia::create(['nombre' => 'Plan'.uniqid(), 'precio' => 1000, 'duracion_dias' => 30, 'horas_diarias_max' => null, 'estado' => 'activa']);

        return Socio::create($override + [
            'membresia_id' => $plan->id, 'user_id' => $user->id, 'nombre' => 'Ana', 'apellido' => 'Test',
            'dni' => '30111222', 'estado' => 'activo', 'fecha_vencimiento' => today()->addDays(10),
        ]);
    }

    public function test_invitado_es_redirigido_al_login_del_portal(): void
    {
        $this->get('/socio')->assertRedirect('/socio/login');
    }

    public function test_socio_ingresa_con_dni_y_pin_y_ve_su_cuenta(): void
    {
        $s = $this->socio();
        AptoFisico::create(['socio_id' => $s->id, 'user_id' => $s->user_id, 'fecha_emision' => today()->subMonth(),
            'fecha_vencimiento' => today()->addMonths(6), 'estado' => 'vigente']);
        Asistencia::create(['socio_id' => $s->id, 'fecha' => today(), 'hora_ingreso' => '08:00:00',
            'hora_salida' => '09:30:00', 'tiempo_permanencia' => '01:30:00']);

        $this->post('/socio/login', ['dni' => $s->dni, 'pin' => $s->pin])->assertRedirect('/socio');

        $this->get('/socio')->assertOk()
            ->assertSee('Hola, Ana')
            ->assertSee('Al día')
            ->assertSee('Vigente')
            ->assertSee('1 h 30 min');
    }

    public function test_pin_incorrecto_es_rechazado(): void
    {
        $s = $this->socio();
        $otro = $s->pin === '000000' ? '111111' : '000000';

        $this->post('/socio/login', ['dni' => $s->dni, 'pin' => $otro])->assertSessionHasErrors('dni');
        $this->get('/socio')->assertRedirect('/socio/login');
    }

    public function test_socio_inactivo_no_puede_ingresar(): void
    {
        $s = $this->socio(['estado' => 'inactivo']);

        $this->post('/socio/login', ['dni' => $s->dni, 'pin' => $s->pin])->assertSessionHasErrors('dni');
    }

    public function test_bloquea_el_dni_tras_varios_intentos_fallidos(): void
    {
        $s = $this->socio();
        $otro = $s->pin === '000000' ? '111111' : '000000';

        for ($i = 0; $i < 5; $i++) {
            $this->post('/socio/login', ['dni' => $s->dni, 'pin' => $otro]);
        }

        // incluso con el PIN correcto queda bloqueado
        $this->post('/socio/login', ['dni' => $s->dni, 'pin' => $s->pin])->assertSessionHasErrors('dni');
        $this->get('/socio')->assertRedirect('/socio/login');
    }

    public function test_el_socio_solo_ve_sus_propias_asistencias(): void
    {
        $s = $this->socio();
        $otro = Socio::create(['membresia_id' => $s->membresia_id, 'user_id' => $s->user_id, 'nombre' => 'Luis', 'apellido' => 'Otro',
            'dni' => '30999888', 'estado' => 'activo']);
        Asistencia::create(['socio_id' => $otro->id, 'fecha' => today()->subDay(), 'hora_ingreso' => '07:15:00']);

        $this->withSession(['socio_id' => $s->id])->get('/socio')
            ->assertOk()->assertDontSee('07:15')->assertSee('Todavía no registrás asistencias');
    }

    public function test_dado_de_baja_pierde_la_sesion(): void
    {
        $s = $this->socio();
        $s->delete();

        $this->withSession(['socio_id' => $s->id])->get('/socio')->assertRedirect('/socio/login');
    }

    public function test_salir_cierra_la_sesion_del_socio(): void
    {
        $s = $this->socio();

        $this->withSession(['socio_id' => $s->id])->post('/socio/logout')->assertRedirect('/socio/login');
        $this->get('/socio')->assertRedirect('/socio/login');
    }

    public function test_la_sesion_del_socio_no_da_acceso_al_panel(): void
    {
        $s = $this->socio();

        $this->withSession(['socio_id' => $s->id])->get('/socios')->assertRedirect('/login');
    }
}
