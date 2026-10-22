<?php

namespace Tests\Feature;

use App\Models\AptoFisico;
use App\Models\Membresia;
use App\Models\Socio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

/** Validación con Form Requests y permisos por registro con Policies (socios, aptos y membresías). */
class GestionTest extends TestCase
{
    use RefreshDatabase;

    protected function plan(string $estado = 'activa'): Membresia
    {
        return Membresia::create(['nombre' => 'Plan'.uniqid(), 'precio' => 1000, 'duracion_dias' => 30, 'estado' => $estado]);
    }

    protected function socio(): Socio
    {
        return Socio::create([
            'membresia_id' => $this->plan()->id, 'user_id' => User::factory()->create()->id, 'nombre' => 'Ana',
            'apellido' => 'Test', 'dni' => (string) random_int(20000000, 49999999), 'estado' => 'activo',
            'consentimiento_datos_at' => now(),
        ]);
    }

    protected function datosSocio(array $override = []): array
    {
        return $override + ['nombre' => 'Juan', 'apellido' => 'Pérez', 'dni' => '30111222', 'membresia_id' => $this->plan()->id, 'consentimiento_datos' => '1'];
    }

    public function test_alta_de_socio_valida_dni_unico_y_membresia_activa(): void
    {
        $recepcion = User::factory()->create();
        $existente = $this->socio();

        $this->actingAs($recepcion)->post('/socios', $this->datosSocio(['dni' => $existente->dni]))
            ->assertSessionHasErrors('dni');
        $this->actingAs($recepcion)->post('/socios', $this->datosSocio(['membresia_id' => $this->plan('inactiva')->id]))
            ->assertSessionHasErrors('membresia_id');

        $this->actingAs($recepcion)->post('/socios', $this->datosSocio())->assertSessionHasNoErrors();
        $this->assertDatabaseHas('socios', ['dni' => '30111222', 'user_id' => $recepcion->id, 'estado' => 'activo']);
    }

    public function test_al_editar_se_conserva_el_propio_dni_y_una_membresia_desactivada(): void
    {
        $socio = $this->socio();
        $socio->membresia->update(['estado' => 'inactiva']);

        $this->actingAs(User::factory()->create())
            ->put("/socios/{$socio->id}", ['nombre' => 'Ana María', 'apellido' => 'Test', 'dni' => $socio->dni, 'membresia_id' => $socio->membresia_id])
            ->assertSessionHasNoErrors();

        $this->assertSame('Ana María', $socio->fresh()->nombre);
    }

    public function test_el_apto_exige_vencimiento_posterior_a_la_emision(): void
    {
        $socio = $this->socio();
        $recepcion = User::factory()->create();

        $this->actingAs($recepcion)->post("/socios/{$socio->id}/aptos", [
            'fecha_emision' => today()->toDateString(), 'fecha_vencimiento' => today()->subDay()->toDateString(),
        ])->assertSessionHasErrors('fecha_vencimiento');
        $this->actingAs($recepcion)->post("/socios/{$socio->id}/aptos", [
            'fecha_emision' => today()->addDay()->toDateString(), 'fecha_vencimiento' => today()->addYear()->toDateString(),
        ])->assertSessionHasErrors('fecha_emision');

        $this->assertSame(0, $socio->aptos()->count());
    }

    public function test_la_membresia_valida_precio_y_tope_de_horas(): void
    {
        $admin = User::factory()->administrador()->create();

        $this->actingAs($admin)->post('/membresias', ['nombre' => 'Mala', 'precio' => -1, 'duracion_dias' => 30, 'horas_diarias_max' => 25, 'estado' => 'activa'])
            ->assertSessionHasErrors(['precio', 'horas_diarias_max']);
        $this->actingAs($admin)->post('/membresias', ['nombre' => 'Libre', 'precio' => 5000, 'duracion_dias' => 30, 'estado' => 'activa'])
            ->assertSessionHasNoErrors();

        $this->assertNull(Membresia::where('nombre', 'Libre')->value('horas_diarias_max'));
    }

    public function test_solo_el_administrador_da_de_baja_y_reactiva_segun_el_estado_del_socio(): void
    {
        $admin = User::factory()->administrador()->create();
        $recepcion = User::factory()->create();
        $socio = $this->socio();

        $this->assertTrue(Gate::forUser($admin)->allows('delete', $socio));
        $this->assertTrue(Gate::forUser($recepcion)->denies('delete', $socio));
        // Reactivar a un socio que no está dado de baja no tiene sentido.
        $this->actingAs($admin)->post("/socios/{$socio->id}/reactivar")->assertForbidden();

        $this->actingAs($admin)->delete("/socios/{$socio->id}")->assertRedirect('/socios');
        $socio->refresh();
        $this->assertTrue($socio->trashed());
        $this->assertTrue(Gate::forUser($admin)->denies('delete', $socio));
        $this->assertTrue(Gate::forUser($admin)->allows('restore', $socio));

        $this->actingAs($admin)->post("/socios/{$socio->id}/reactivar")->assertRedirect();
        $this->assertFalse($socio->fresh()->trashed());
    }

    public function test_la_ficha_muestra_los_botones_que_permite_la_policy(): void
    {
        $socio = $this->socio();

        $this->actingAs(User::factory()->administrador()->create())->get("/socios/{$socio->id}")
            ->assertSee('Dar de baja')->assertDontSee('Reactivar');
        $this->actingAs(User::factory()->create())->get("/socios/{$socio->id}")
            ->assertDontSee('Dar de baja')->assertDontSee('Reactivar');
    }

    public function test_el_profesor_no_puede_ver_ni_cargar_aptos_fisicos(): void
    {
        $socio = $this->socio();
        $apto = AptoFisico::create(['socio_id' => $socio->id, 'user_id' => $socio->user_id, 'fecha_emision' => today(),
            'fecha_vencimiento' => today()->addYear(), 'estado' => 'vigente']);
        $profesor = User::factory()->create(['rol' => User::ROL_PROFESOR]);

        $this->assertTrue(Gate::forUser($profesor)->denies('view', $apto));
        $this->assertTrue(Gate::forUser($profesor)->denies('create', AptoFisico::class));
        $this->assertTrue(Gate::forUser(User::factory()->create())->allows('view', $apto));
    }

    public function test_pagos_operadores_y_configuracion_validan_sus_datos(): void
    {
        $admin = User::factory()->administrador()->create();
        $socio = $this->socio();

        $this->actingAs($admin)->post("/socios/{$socio->id}/pagos", ['membresia_id' => $socio->membresia_id, 'metodo_pago' => 'cheque'])
            ->assertSessionHasErrors('metodo_pago');

        $this->actingAs($admin)->post('/operadores', ['name' => 'Rocío', 'email' => $admin->email, 'rol' => User::ROL_RECEPCION])
            ->assertSessionHasErrors(['email', 'password']);
        $this->actingAs($admin)->post('/operadores', ['name' => 'Rocío', 'email' => 'rocio@gym.test', 'password' => 'secreta123',
            'password_confirmation' => 'secreta123', 'rol' => User::ROL_RECEPCION])->assertSessionHasNoErrors();
        // Al editar, la contraseña vacía significa "no cambiarla".
        $rocio = User::where('email', 'rocio@gym.test')->first();
        $this->actingAs($admin)->put("/operadores/{$rocio->id}", ['name' => 'Rocío P.', 'email' => $rocio->email, 'rol' => User::ROL_RECEPCION, 'activo' => '1'])
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)->put('/configuracion', ['capacidad_maxima' => 0, 'instagram' => 'no válido'])
            ->assertSessionHasErrors(['capacidad_maxima', 'instagram']);
    }
}
