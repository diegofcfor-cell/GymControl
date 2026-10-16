<?php

namespace Tests\Feature;

use App\Livewire\ChatbotFaq;
use App\Models\Asistencia;
use App\Models\Membresia;
use App\Models\PreguntaFrecuente;
use App\Models\Socio;
use App\Models\User;
use App\Services\ChatbotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChatbotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        PreguntaFrecuente::create(['pregunta' => '¿Cuál es el horario del gimnasio?', 'respuesta' => 'Lunes a viernes de 7 a 23 h.',
            'palabras_clave' => 'horario, abren, cierran, domingo', 'orden' => 1]);
        PreguntaFrecuente::create(['pregunta' => '¿Tienen vestuarios y duchas?', 'respuesta' => 'Sí, con lockers.',
            'palabras_clave' => 'vestuario, ducha, locker', 'orden' => 2]);
        PreguntaFrecuente::create(['pregunta' => '¿Hay estacionamiento?', 'respuesta' => 'Oculta', 'palabras_clave' => 'auto, estacionar', 'activa' => false]);
    }

    protected function socio(): Socio
    {
        $user = User::factory()->create();
        $plan = Membresia::create(['nombre' => 'Premium', 'precio' => 50000, 'duracion_dias' => 30, 'horas_diarias_max' => null, 'estado' => 'activa']);

        return Socio::create(['membresia_id' => $plan->id, 'user_id' => $user->id, 'nombre' => 'Ana', 'apellido' => 'Test',
            'dni' => (string) random_int(20000000, 49999999), 'estado' => 'activo', 'fecha_vencimiento' => today()->addDays(10)]);
    }

    public function test_encuentra_la_respuesta_por_palabras_clave_sin_tildes_ni_plurales(): void
    {
        $bot = app(ChatbotService::class);

        $this->assertSame('Lunes a viernes de 7 a 23 h.', $bot->responder('a que hora abren el domingo?')['respuesta']);
        $this->assertSame('Lunes a viernes de 7 a 23 h.', $bot->responder('HORARIOS')['respuesta']);
        $this->assertSame('Sí, con lockers.', $bot->responder('hay duchas?')['respuesta']);
    }

    public function test_no_usa_preguntas_ocultas(): void
    {
        $r = app(ChatbotService::class)->responder('donde dejo el auto');

        $this->assertFalse($r['encontrada']);
        $this->assertNotContains('¿Hay estacionamiento?', $r['sugerencias']);
    }

    public function test_sin_coincidencia_deriva_a_recepcion_con_sugerencias(): void
    {
        $r = app(ChatbotService::class)->responder('quiero un batido de proteínas');

        $this->assertFalse($r['encontrada']);
        $this->assertStringContainsString('recepción', $r['respuesta']);
        $this->assertContains('¿Cuál es el horario del gimnasio?', $r['sugerencias']);
    }

    public function test_responde_precios_con_las_membresias_activas(): void
    {
        Membresia::create(['nombre' => 'Básica', 'precio' => 25000, 'duracion_dias' => 30, 'horas_diarias_max' => 2, 'estado' => 'activa']);
        Membresia::create(['nombre' => 'Vieja', 'precio' => 1000, 'duracion_dias' => 30, 'horas_diarias_max' => 1, 'estado' => 'inactiva']);

        $r = app(ChatbotService::class)->responder('¿Cuánto cuesta la cuota?');

        $this->assertStringContainsString('Básica: $ 25.000', $r['respuesta']);
        $this->assertStringContainsString('hasta 2 h por día', $r['respuesta']);
        $this->assertStringNotContainsString('Vieja', $r['respuesta']);
    }

    public function test_responde_cuanta_gente_hay_ahora(): void
    {
        $s = $this->socio();
        Asistencia::create(['socio_id' => $s->id, 'fecha' => today(), 'hora_ingreso' => '00:00:00']);

        $r = app(ChatbotService::class)->responder('hay mucha gente?');

        $this->assertStringContainsString('Ahora hay 1 persona en el gimnasio', $r['respuesta']);
    }

    public function test_el_socio_conversa_desde_su_portal(): void
    {
        $s = $this->socio();

        $this->get('/socio/preguntas')->assertRedirect('/socio/login');
        $this->withSession(['socio_id' => $s->id])->get('/socio/preguntas')
            ->assertOk()->assertSee('¿Tienen vestuarios y duchas?')->assertDontSee('¿Hay estacionamiento?');

        $this->withSession(['socio_id' => $s->id]);
        Livewire::test(ChatbotFaq::class)
            ->set('mensaje', 'horario?')
            ->call('enviar')
            ->assertSet('mensaje', '')
            ->assertSee('Lunes a viernes de 7 a 23 h.');
    }

    public function test_solo_el_administrador_gestiona_las_preguntas(): void
    {
        $this->actingAs(User::factory()->create())->get('/preguntas')->assertForbidden();

        $admin = User::factory()->administrador()->create();
        $this->actingAs($admin)->get('/preguntas')->assertOk()->assertSee('¿Hay estacionamiento?');
        $this->actingAs($admin)->post('/preguntas', ['pregunta' => '¿Se puede ir con mascotas?', 'respuesta' => 'No.', 'activa' => '1'])
            ->assertRedirect('/preguntas');
        $nueva = PreguntaFrecuente::where('pregunta', '¿Se puede ir con mascotas?')->firstOrFail();
        $this->assertTrue($nueva->activa);
        $this->assertSame(0, $nueva->orden);

        $this->actingAs($admin)->put("/preguntas/{$nueva->id}", ['pregunta' => '¿Mascotas?', 'respuesta' => 'No.'])->assertRedirect('/preguntas');
        $this->assertFalse($nueva->fresh()->activa);

        $this->actingAs($admin)->delete("/preguntas/{$nueva->id}")->assertRedirect('/preguntas');
        $this->assertNull($nueva->fresh());
    }
}
