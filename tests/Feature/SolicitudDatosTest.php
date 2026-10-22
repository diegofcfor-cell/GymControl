<?php

namespace Tests\Feature;

use App\Models\Membresia;
use App\Models\Socio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** HU27: el socio pide rectificar o suprimir sus datos desde el portal y el administrador responde (Ley 25.326, art. 16). */
class SolicitudDatosTest extends TestCase
{
    use RefreshDatabase;

    protected function socio(string $dni = '30111222'): Socio
    {
        $plan = Membresia::create(['nombre' => 'Plan'.uniqid(), 'precio' => 1000, 'duracion_dias' => 30, 'estado' => 'activa']);

        return Socio::create([
            'membresia_id' => $plan->id, 'user_id' => User::factory()->create()->id, 'nombre' => 'Ana', 'apellido' => 'Test',
            'dni' => $dni, 'telefono' => '3704111222', 'estado' => 'activo', 'consentimiento_datos_at' => now(),
        ]);
    }

    public function test_el_socio_ve_sus_datos_y_pide_una_rectificacion(): void
    {
        $socio = $this->socio();

        $this->withSession(['socio_id' => $socio->id])->get('/socio/mis-datos')
            ->assertOk()->assertSee('30111222')->assertSee('3704111222')->assertSee('Aceptaste el aviso de privacidad');

        $this->withSession(['socio_id' => $socio->id])
            ->post('/socio/mis-datos/solicitud', ['tipo' => 'rectificacion', 'detalle' => 'Mi teléfono ahora es 3704999888.'])
            ->assertRedirect('/socio/mis-datos');

        $this->assertDatabaseHas('solicitudes_datos', ['socio_id' => $socio->id, 'tipo' => 'rectificacion', 'estado' => 'pendiente']);
    }

    public function test_valida_el_pedido_y_no_duplica_uno_pendiente_del_mismo_tipo(): void
    {
        $socio = $this->socio();
        $sesion = $this->withSession(['socio_id' => $socio->id]);

        $sesion->post('/socio/mis-datos/solicitud', ['tipo' => 'borrar_todo', 'detalle' => 'corto'])
            ->assertSessionHasErrors(['tipo', 'detalle']);

        $datos = ['tipo' => 'supresion', 'detalle' => 'Quiero que eliminen mis datos.'];
        $sesion->post('/socio/mis-datos/solicitud', $datos);
        $sesion->post('/socio/mis-datos/solicitud', $datos);
        $sesion->post('/socio/mis-datos/solicitud', ['tipo' => 'rectificacion', 'detalle' => 'Corrijan mi apellido, es Tést.']);

        $this->assertSame(1, $socio->solicitudesDatos()->where('tipo', 'supresion')->count());
        $this->assertSame(2, $socio->solicitudesDatos()->count());
    }

    public function test_el_socio_solo_ve_sus_propios_pedidos(): void
    {
        $socio = $this->socio();
        $otro = $this->socio('30999888');
        $otro->solicitudesDatos()->create(['tipo' => 'rectificacion', 'detalle' => 'Pedido del otro socio']);

        $this->withSession(['socio_id' => $socio->id])->get('/socio/mis-datos')
            ->assertDontSee('Pedido del otro socio')->assertSee('Todavía no hiciste pedidos.');
    }

    public function test_el_plazo_es_de_cinco_dias_habiles(): void
    {
        Carbon::setTestNow('2026-10-23 10:00'); // viernes
        $solicitud = $this->socio()->solicitudesDatos()->create(['tipo' => 'rectificacion', 'detalle' => 'Corregir el correo']);

        $this->assertSame('2026-10-30', $solicitud->plazo()->toDateString()); // el viernes siguiente
        $this->assertFalse($solicitud->estaVencida());

        Carbon::setTestNow('2026-10-31');
        $this->assertTrue($solicitud->fresh()->estaVencida());
        Carbon::setTestNow();
    }

    public function test_el_administrador_responde_y_el_socio_ve_la_respuesta(): void
    {
        $admin = User::factory()->administrador()->create();
        $socio = $this->socio();
        $solicitud = $socio->solicitudesDatos()->create(['tipo' => 'rectificacion', 'detalle' => 'Mi teléfono cambió']);

        $this->actingAs($admin)->get('/solicitudes-datos')->assertOk()->assertSee('Mi teléfono cambió');
        $this->actingAs($admin)->put("/solicitudes-datos/{$solicitud->id}", ['respuesta' => ''])->assertSessionHasErrors('respuesta');
        $this->actingAs($admin)->put("/solicitudes-datos/{$solicitud->id}", ['respuesta' => 'Actualizamos tu teléfono.'])
            ->assertRedirect('/solicitudes-datos');

        $solicitud->refresh();
        $this->assertSame('resuelta', $solicitud->estado);
        $this->assertSame($admin->id, $solicitud->user_id);
        $this->assertNotNull($solicitud->resuelta_at);

        // Una respuesta ya enviada no se reescribe.
        $this->actingAs($admin)->put("/solicitudes-datos/{$solicitud->id}", ['respuesta' => 'Otra'])->assertForbidden();

        $this->withSession(['socio_id' => $socio->id])->get('/socio/mis-datos')->assertSee('Actualizamos tu teléfono.');
    }

    public function test_recepcion_y_profesor_no_ven_los_pedidos(): void
    {
        $solicitud = $this->socio()->solicitudesDatos()->create(['tipo' => 'supresion', 'detalle' => 'Eliminar mis datos']);

        foreach ([User::factory()->create(), User::factory()->create(['rol' => User::ROL_PROFESOR])] as $operador) {
            $this->actingAs($operador)->get('/solicitudes-datos')->assertForbidden();
            $this->actingAs($operador)->put("/solicitudes-datos/{$solicitud->id}", ['respuesta' => 'x'])->assertForbidden();
        }
        $this->assertSame('pendiente', $solicitud->fresh()->estado);
    }
}
