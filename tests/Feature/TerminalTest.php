<?php

namespace Tests\Feature;

use App\Livewire\PantallaAcceso;
use App\Models\AptoFisico;
use App\Models\Membresia;
use App\Models\Socio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** HU31: la tablet o PC de la entrada usa un rol que solo abre la pantalla de acceso (mínimo privilegio). */
class TerminalTest extends TestCase
{
    use RefreshDatabase;

    protected function terminal(): User
    {
        return User::factory()->create(['rol' => User::ROL_TERMINAL, 'password' => 'Entrada123']);
    }

    public function test_al_iniciar_sesion_la_terminal_va_directo_a_la_pantalla_de_acceso(): void
    {
        $t = $this->terminal();

        $this->post('/login', ['email' => $t->email, 'password' => 'Entrada123'])->assertRedirect('/panel');
        $this->get('/panel')->assertRedirect('/acceso');
        $this->get('/acceso')->assertOk()->assertDontSee('Volver al sistema');
    }

    public function test_la_terminal_no_ve_socios_pagos_ni_ninguna_otra_seccion(): void
    {
        $t = $this->terminal();

        foreach (['/recepcion', '/socios', '/socios/crear', '/pagos', '/asistencias', '/dashboard', '/operadores', '/rutinas', '/configuracion'] as $ruta) {
            $this->actingAs($t)->get($ruta)->assertForbidden();
        }
    }

    public function test_la_terminal_registra_el_ingreso_con_el_pin(): void
    {
        $recep = User::factory()->create();
        $plan = Membresia::create(['nombre' => 'Básica', 'precio' => 1000, 'duracion_dias' => 30, 'estado' => 'activa']);
        $socio = Socio::create(['membresia_id' => $plan->id, 'user_id' => $recep->id, 'nombre' => 'Ana', 'apellido' => 'Test',
            'dni' => '30111222', 'estado' => 'activo', 'fecha_vencimiento' => today()->addDays(10)]);
        AptoFisico::create(['socio_id' => $socio->id, 'user_id' => $recep->id, 'fecha_emision' => today()->subMonth(),
            'fecha_vencimiento' => today()->addMonths(6), 'estado' => 'vigente']);

        Livewire::actingAs($this->terminal())->test(PantallaAcceso::class)
            ->set('codigo', $socio->pin)->call('registrar')
            ->assertSet('resultado.ok', true)->assertSet('resultado.tipo', 'ingreso');

        $this->assertDatabaseHas('asistencias', ['socio_id' => $socio->id, 'hora_salida' => null]);
    }

    public function test_el_administrador_crea_una_terminal_desde_operadores(): void
    {
        $admin = User::factory()->administrador()->create();

        $this->actingAs($admin)->get('/operadores/create')->assertSee('Terminal de acceso');
        $this->actingAs($admin)->post('/operadores', ['name' => 'Tablet de la entrada', 'email' => 'tablet@gym.test', 'password' => 'Entrada123',
            'password_confirmation' => 'Entrada123', 'rol' => User::ROL_TERMINAL])->assertSessionHasNoErrors();

        $this->assertTrue(User::where('email', 'tablet@gym.test')->first()->esTerminal());
    }

    public function test_desactivar_la_terminal_la_saca_de_la_pantalla_de_acceso(): void
    {
        $t = $this->terminal();
        $t->update(['activo' => false]);

        $this->actingAs($t)->get('/acceso')->assertRedirect('/login');
    }
}
