<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitado_es_redirigido_al_login(): void
    {
        $this->get('/socios')->assertRedirect('/login');
        $this->get('/acceso')->assertRedirect('/login');
    }

    public function test_login_correcto_e_incorrecto(): void
    {
        $u = User::factory()->administrador()->create(['password' => 'secreta123']);

        $this->post('/login', ['email' => $u->email, 'password' => 'mala'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => $u->email, 'password' => 'secreta123'])->assertRedirect('/panel');
        $this->assertAuthenticatedAs($u);
    }

    public function test_usuario_inactivo_no_puede_ingresar(): void
    {
        $u = User::factory()->create(['password' => 'secreta123', 'activo' => false]);
        $this->post('/login', ['email' => $u->email, 'password' => 'secreta123'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_recepcionista_no_accede_a_secciones_de_administrador(): void
    {
        $r = User::factory()->create();
        foreach (['/dashboard', '/membresias', '/operadores'] as $ruta) {
            $this->actingAs($r)->get($ruta)->assertForbidden();
        }
        $this->actingAs($r)->get('/socios')->assertOk();
    }

    public function test_administrador_accede_a_todo(): void
    {
        $a = User::factory()->administrador()->create();
        foreach (['/dashboard', '/membresias', '/operadores', '/socios', '/pagos', '/asistencias', '/acceso'] as $ruta) {
            $this->actingAs($a)->get($ruta)->assertOk();
        }
    }
}
