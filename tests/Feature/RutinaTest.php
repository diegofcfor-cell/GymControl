<?php

namespace Tests\Feature;

use App\Models\Membresia;
use App\Models\Rutina;
use App\Models\Socio;
use App\Models\SolicitudRutina;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RutinaTest extends TestCase
{
    use RefreshDatabase;

    protected User $profesor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->profesor = User::factory()->create(['rol' => User::ROL_PROFESOR, 'name' => 'Pablo Profe']);
    }

    protected function socio(string $nombre = 'Ana'): Socio
    {
        $plan = Membresia::create(['nombre' => 'Plan'.uniqid(), 'precio' => 1000, 'duracion_dias' => 30, 'horas_diarias_max' => null, 'estado' => 'activa']);

        return Socio::create(['membresia_id' => $plan->id, 'user_id' => $this->profesor->id, 'nombre' => $nombre, 'apellido' => 'Test',
            'dni' => (string) random_int(20000000, 49999999), 'estado' => 'activo']);
    }

    protected function pedido(Socio $s): SolicitudRutina
    {
        return $s->solicitudesRutina()->create(['objetivo' => 'Tonificar', 'dias_por_semana' => 2, 'nivel' => 'principiante', 'estado' => 'pendiente']);
    }

    protected function datosRutina(array $extra = []): array
    {
        return $extra + [
            'nombre' => 'Full body', 'objetivo' => 'Tonificar', 'observaciones' => 'Entrar en calor 10 min.',
            'ejercicios' => [
                ['dia' => 'Día 1', 'ejercicio' => 'Sentadilla', 'series' => 3, 'repeticiones' => '12', 'descanso' => '60 s', 'notas' => ''],
                ['dia' => 'Día 1', 'ejercicio' => '', 'series' => null, 'repeticiones' => '', 'descanso' => '', 'notas' => ''], // fila vacía
                ['dia' => 'Día 2', 'ejercicio' => 'Remo con mancuerna', 'series' => 4, 'repeticiones' => '10', 'descanso' => '', 'notas' => 'Espalda recta'],
            ],
        ];
    }

    public function test_el_socio_pide_una_rutina_una_sola_vez_hasta_que_la_atiendan(): void
    {
        $s = $this->socio();

        $this->withSession(['socio_id' => $s->id])->get('/socio/rutina')->assertOk()->assertSee('Pedí tu rutina');
        $this->withSession(['socio_id' => $s->id])->post('/socio/rutina/pedido', [
            'objetivo' => 'Bajar de peso', 'dias_por_semana' => 3, 'nivel' => 'intermedio', 'comentarios' => 'Me duele la rodilla',
        ])->assertRedirect('/socio/rutina');
        $this->withSession(['socio_id' => $s->id])->post('/socio/rutina/pedido', [
            'objetivo' => 'Tonificar', 'dias_por_semana' => 2, 'nivel' => 'principiante',
        ])->assertRedirect('/socio/rutina');

        $this->assertSame(1, SolicitudRutina::count());
        $this->withSession(['socio_id' => $s->id])->get('/socio/rutina')->assertSee('Tu pedido está en curso')->assertDontSee('Enviar pedido');
    }

    public function test_valida_el_pedido(): void
    {
        $s = $this->socio();

        $this->withSession(['socio_id' => $s->id])->post('/socio/rutina/pedido', ['objetivo' => 'Ser astronauta', 'dias_por_semana' => 9, 'nivel' => 'experto'])
            ->assertSessionHasErrors(['objetivo', 'dias_por_semana', 'nivel']);
    }

    public function test_el_profesor_ve_los_pedidos_y_arma_la_rutina(): void
    {
        $s = $this->socio();
        $pedido = $this->pedido($s);

        $this->actingAs($this->profesor)->get('/rutinas')->assertOk()->assertSee($s->nombre_completo)->assertSee('Armar rutina');
        // Las filas viajan a Alpine con @js, que escapa la tilde de "Día".
        $this->actingAs($this->profesor)->get("/rutinas/pedidos/{$pedido->id}/armar")->assertOk()->assertSee('días por semana')->assertSee('D\\\\u00eda 2', false);
        $this->actingAs($this->profesor)->post("/rutinas/pedidos/{$pedido->id}", $this->datosRutina())->assertRedirect('/rutinas');

        $rutina = Rutina::firstOrFail();
        $this->assertSame(['Sentadilla', 'Remo con mancuerna'], $rutina->ejercicios->pluck('ejercicio')->all());
        $this->assertSame($this->profesor->id, $rutina->user_id);
        $this->assertSame('atendida', $pedido->fresh()->estado);
        $this->assertSame($rutina->id, $pedido->fresh()->rutina_id);

        // Un pedido ya atendido no se puede volver a usar.
        $this->actingAs($this->profesor)->get("/rutinas/pedidos/{$pedido->id}/armar")->assertNotFound();

        auth()->logout();
        $this->withSession(['socio_id' => $s->id])->get('/socio/rutina')->assertOk()
            ->assertSee('Full body')->assertSee('Remo con mancuerna')->assertSee('4 series · 10 rep.')->assertSee('Espalda recta')->assertSee('Pablo Profe');
    }

    public function test_la_rutina_nueva_reemplaza_a_la_anterior(): void
    {
        $s = $this->socio();
        $this->actingAs($this->profesor)->post('/rutinas/pedidos/'.$this->pedido($s)->id, $this->datosRutina(['nombre' => 'Vieja']));
        $this->actingAs($this->profesor)->post('/rutinas/pedidos/'.$this->pedido($s)->id, $this->datosRutina(['nombre' => 'Nueva']));

        $this->assertSame('Nueva', $s->rutinaActiva()->nombre);
        $this->assertFalse(Rutina::where('nombre', 'Vieja')->first()->activa);
    }

    public function test_exige_al_menos_un_ejercicio_y_permite_editar(): void
    {
        $s = $this->socio();
        $pedido = $this->pedido($s);

        $this->actingAs($this->profesor)->post("/rutinas/pedidos/{$pedido->id}", ['nombre' => 'X', 'ejercicios' => [['dia' => 'Día 1', 'ejercicio' => '']]])
            ->assertSessionHasErrors('ejercicios');

        $this->actingAs($this->profesor)->post("/rutinas/pedidos/{$pedido->id}", $this->datosRutina());
        $rutina = Rutina::firstOrFail();
        $this->actingAs($this->profesor)->get("/rutinas/{$rutina->id}/editar")->assertOk()->assertSee('Sentadilla');
        $this->actingAs($this->profesor)->put("/rutinas/{$rutina->id}", [
            'nombre' => 'Full body v2', 'ejercicios' => [['dia' => 'Día 1', 'ejercicio' => 'Plancha', 'repeticiones' => '30 s']],
        ])->assertRedirect('/rutinas');

        $this->assertSame('Full body v2', $rutina->fresh()->nombre);
        $this->assertSame(['Plancha'], $rutina->fresh()->ejercicios->pluck('ejercicio')->all());
    }

    public function test_permisos_por_rol(): void
    {
        $recepcion = User::factory()->create();
        $admin = User::factory()->administrador()->create();

        $this->actingAs($recepcion)->get('/rutinas')->assertForbidden();
        $this->actingAs($admin)->get('/rutinas')->assertOk();

        // El profesor solo accede a rutinas.
        foreach (['/socios', '/acceso', '/pagos', '/dashboard'] as $ruta) {
            $this->actingAs($this->profesor)->get($ruta)->assertForbidden();
        }
        $this->actingAs($this->profesor)->get('/panel')->assertRedirect('/rutinas');
        $this->actingAs($this->profesor)->get('/')->assertOk()->assertSee('Ir al panel');
        $this->actingAs($this->profesor)->post('/logout')->assertRedirect('/login');
    }

    public function test_el_admin_puede_crear_un_profesor(): void
    {
        $admin = User::factory()->administrador()->create();

        $this->actingAs($admin)->post('/operadores', [
            'name' => 'Laura Profe', 'email' => 'laura@gym.test', 'password' => 'secreta123', 'password_confirmation' => 'secreta123', 'rol' => 'profesor',
        ])->assertRedirect('/operadores');

        $this->assertTrue(User::where('email', 'laura@gym.test')->first()->esProfesor());
    }
}
