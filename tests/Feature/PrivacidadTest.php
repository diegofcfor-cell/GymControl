<?php

namespace Tests\Feature;

use App\Models\Membresia;
use App\Models\Socio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Ley 25.326: aviso de privacidad público y consentimiento antes de tratar datos de salud. */
class PrivacidadTest extends TestCase
{
    use RefreshDatabase;

    protected function plan(): Membresia
    {
        return Membresia::create(['nombre' => 'Plan'.uniqid(), 'precio' => 1000, 'duracion_dias' => 30, 'estado' => 'activa']);
    }

    protected function socioSinConsentimiento(): Socio
    {
        return Socio::create([
            'membresia_id' => $this->plan()->id, 'user_id' => User::factory()->create()->id, 'nombre' => 'Ana',
            'apellido' => 'Test', 'dni' => '28999111', 'estado' => 'activo',
        ]);
    }

    protected function apto(): array
    {
        return ['fecha_emision' => today()->toDateString(), 'fecha_vencimiento' => today()->addYear()->toDateString()];
    }

    public function test_el_aviso_de_privacidad_es_publico_y_se_enlaza_desde_la_web_y_el_portal(): void
    {
        $this->get('/privacidad')->assertOk()->assertSee('Ley 25.326')->assertSee('datos de salud')->assertSee('Agencia de Acceso a la Información Pública');
        $this->get('/')->assertSee(route('privacidad'));
        $this->get('/socio/login')->assertSee(route('privacidad'));
    }

    public function test_el_alta_exige_aceptar_el_aviso_y_guarda_la_fecha(): void
    {
        $recepcion = User::factory()->create();
        $datos = ['nombre' => 'Juan', 'apellido' => 'Pérez', 'dni' => '30111222', 'membresia_id' => $this->plan()->id];

        $this->actingAs($recepcion)->post('/socios', $datos)->assertSessionHasErrors('consentimiento_datos');
        $this->assertDatabaseMissing('socios', ['dni' => '30111222']);

        $this->actingAs($recepcion)->post('/socios', $datos + ['consentimiento_datos' => '1'])->assertSessionHasNoErrors();
        $this->assertTrue(Socio::where('dni', '30111222')->first()->dioConsentimiento());
    }

    public function test_sin_consentimiento_no_se_carga_el_apto_fisico(): void
    {
        $socio = $this->socioSinConsentimiento();

        $this->actingAs(User::factory()->create())->post("/socios/{$socio->id}/aptos", $this->apto())
            ->assertSessionHasErrors('consentimiento');
        $this->assertSame(0, $socio->aptos()->count());

        $this->actingAs(User::factory()->create())->get("/socios/{$socio->id}")
            ->assertSee('Registrar consentimiento')->assertDontSee('Cargar apto físico');
    }

    public function test_al_editar_se_registra_el_consentimiento_de_un_socio_anterior_al_aviso(): void
    {
        $socio = $this->socioSinConsentimiento();
        $recepcion = User::factory()->create();
        $datos = ['nombre' => 'Ana', 'apellido' => 'Test', 'dni' => $socio->dni, 'membresia_id' => $socio->membresia_id];

        // Editar sin marcar la casilla no inventa un consentimiento.
        $this->actingAs($recepcion)->put("/socios/{$socio->id}", $datos)->assertSessionHasNoErrors();
        $this->assertFalse($socio->fresh()->dioConsentimiento());

        $this->actingAs($recepcion)->put("/socios/{$socio->id}", $datos + ['consentimiento_datos' => '1'])->assertSessionHasNoErrors();
        $this->assertTrue($socio->fresh()->dioConsentimiento());

        $this->actingAs($recepcion)->post("/socios/{$socio->id}/aptos", $this->apto())->assertSessionHasNoErrors();
        $this->assertSame(1, $socio->aptos()->count());
    }
}
