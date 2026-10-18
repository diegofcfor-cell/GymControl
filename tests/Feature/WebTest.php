<?php

namespace Tests\Feature;

use App\Models\Configuracion;
use App\Models\Membresia;
use App\Models\User;
use App\Services\ChatbotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_pagina_publica_muestra_planes_activos_y_datos_del_gimnasio(): void
    {
        Membresia::create(['nombre' => 'Premium', 'precio' => 50000, 'duracion_dias' => 30, 'horas_diarias_max' => null, 'estado' => 'activa']);
        Membresia::create(['nombre' => 'Vieja', 'precio' => 1000, 'duracion_dias' => 30, 'horas_diarias_max' => 1, 'estado' => 'inactiva']);
        Configuracion::guardar('horarios', "Lunes a viernes: 7 a 23 h\nSábados: 8 a 13 h");
        Configuracion::guardar('servicios', "Musculación\n\nFuncional");
        Configuracion::guardar('direccion', 'Av. Siempre Viva 742');

        $this->get('/')->assertOk()
            ->assertSee('Premium')->assertSee('$ 50.000')->assertDontSee('Vieja')
            ->assertSee('Sábados: 8 a 13 h')->assertSee('Funcional')->assertSee('Av. Siempre Viva 742')
            ->assertSee('Ahora en el gimnasio')->assertSee('¿Tenés dudas?')
            ->assertSee(route('portal.login'))->assertSee(route('login'));
    }

    public function test_la_portada_usa_las_fotos_de_la_carpeta(): void
    {
        $this->get('/')->assertOk()->assertDontSee('portada-foto');

        $foto = public_path('img/portada/zz-prueba.jpg');
        file_put_contents($foto, 'x');
        try {
            $this->get('/')->assertOk()->assertSee('portada-foto')->assertSee(asset('img/portada/zz-prueba.jpg'));
        } finally {
            unlink($foto);
        }
    }

    public function test_sin_datos_cargados_la_pagina_igual_funciona(): void
    {
        $this->get('/')->assertOk()->assertDontSee('id="horarios"', false)->assertSee('Consultanos en recepción');
    }

    public function test_los_operadores_con_sesion_ven_la_web_con_acceso_al_panel(): void
    {
        $this->get('/')->assertOk()->assertDontSee('Ir al panel');
        $this->actingAs(User::factory()->administrador()->create())->get('/')->assertOk()->assertSee('Ir al panel');
        $this->get('/panel')->assertRedirect('/dashboard');
        $this->get('/socios')->assertOk()->assertSee('Ver web');
    }

    public function test_el_login_de_operadores_enlaza_la_web(): void
    {
        $this->get('/login')->assertOk()->assertSee('Ir a la página del gimnasio');
    }

    public function test_el_admin_carga_los_datos_de_la_web(): void
    {
        $admin = User::factory()->administrador()->create();

        $this->actingAs($admin)->put('/configuracion', [
            'capacidad_maxima' => 40, 'horarios' => 'Todos los días: 8 a 22 h', 'telefono' => '370 411-1111',
            'instagram' => '@gimnasiofenix', 'email' => 'no-es-un-correo',
        ])->assertSessionHasErrors('email');

        $this->actingAs($admin)->put('/configuracion', [
            'capacidad_maxima' => 40, 'horarios' => 'Todos los días: 8 a 22 h', 'telefono' => '370 411-1111', 'instagram' => '@gimnasiofenix',
        ])->assertRedirect('/configuracion');

        $this->assertSame('Todos los días: 8 a 22 h', Configuracion::obtener('horarios'));
        auth()->logout();
        $this->get('/')->assertSee('instagram.com/gimnasiofenix', false);
    }

    public function test_el_chatbot_responde_horarios_y_contacto_cargados(): void
    {
        $bot = app(ChatbotService::class);
        $this->assertFalse($bot->responder('a que hora abren?')['encontrada']);

        Configuracion::guardar('horarios', 'Lunes a viernes: 7 a 23 h');
        Configuracion::guardar('direccion', 'Av. Siempre Viva 742');

        $this->assertStringContainsString('Lunes a viernes: 7 a 23 h', $bot->responder('a que hora abren?')['respuesta']);
        $this->assertStringContainsString('Av. Siempre Viva 742', $bot->responder('donde quedan?')['respuesta']);
    }
}
