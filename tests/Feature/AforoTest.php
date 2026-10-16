<?php

namespace Tests\Feature;

use App\Models\Asistencia;
use App\Models\Configuracion;
use App\Models\Membresia;
use App\Models\Socio;
use App\Models\User;
use App\Services\AforoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AforoTest extends TestCase
{
    use RefreshDatabase;

    protected function socio(): Socio
    {
        $user = User::factory()->create();
        $plan = Membresia::create(['nombre' => 'Plan'.uniqid(), 'precio' => 1000, 'duracion_dias' => 30, 'horas_diarias_max' => null, 'estado' => 'activa']);

        return Socio::create([
            'membresia_id' => $plan->id, 'user_id' => $user->id, 'nombre' => 'Ana', 'apellido' => 'Test',
            'dni' => (string) random_int(20000000, 49999999), 'estado' => 'activo', 'fecha_vencimiento' => today()->addDays(10),
        ]);
    }

    /** Crea $n socios presentes ahora (asistencia de hoy sin egreso). */
    protected function presentes(int $n): void
    {
        for ($i = 0; $i < $n; $i++) {
            Asistencia::create(['socio_id' => $this->socio()->id, 'fecha' => today(), 'hora_ingreso' => '00:00:00']);
        }
    }

    public function test_capacidad_por_defecto_y_configurada(): void
    {
        $aforo = app(AforoService::class);
        $this->assertSame(AforoService::CAPACIDAD_POR_DEFECTO, $aforo->capacidad());

        Configuracion::guardar('capacidad_maxima', '20');
        $this->assertSame(20, $aforo->capacidad());
    }

    public function test_cuenta_solo_presentes_de_hoy_sin_egreso(): void
    {
        $this->presentes(2);
        $s = $this->socio();
        Asistencia::create(['socio_id' => $s->id, 'fecha' => today(), 'hora_ingreso' => '00:00:00', 'hora_salida' => '00:30:00', 'tiempo_permanencia' => '00:30:00']);
        Asistencia::create(['socio_id' => $s->id, 'fecha' => today()->subDay(), 'hora_ingreso' => '10:00:00']);

        $this->assertSame(2, app(AforoService::class)->presentes());
    }

    public function test_nivel_segun_porcentaje_de_capacidad(): void
    {
        Configuracion::guardar('capacidad_maxima', '10');
        $aforo = app(AforoService::class);

        $this->presentes(4);
        $this->assertSame(['presentes' => 4, 'capacidad' => 10, 'porcentaje' => 40, 'nivel' => 'libre'], $aforo->estado());

        $this->presentes(1);
        $this->assertSame('moderado', $aforo->estado()['nivel']);

        $this->presentes(3);
        $this->assertSame('lleno', $aforo->estado()['nivel']);
    }

    public function test_horas_pico_promedia_por_dia_de_la_semana(): void
    {
        $s = $this->socio();
        // Las 4 semanas anteriores, mismo día de la semana que hoy, de 10:00 a 11:30.
        for ($w = 1; $w <= 4; $w++) {
            Asistencia::create(['socio_id' => $s->id, 'fecha' => today()->subWeeks($w), 'hora_ingreso' => '10:00:00',
                'hora_salida' => '11:30:00', 'tiempo_permanencia' => '01:30:00']);
        }
        // Otro día de la semana: no debe sumar.
        Asistencia::create(['socio_id' => $s->id, 'fecha' => today()->subDays(3), 'hora_ingreso' => '10:00:00']);

        $horas = app(AforoService::class)->horasPico(today()->dayOfWeek);

        $this->assertSame(0.0, $horas[9]);
        $this->assertSame(1.0, $horas[10]);
        $this->assertSame(1.0, $horas[11]);
        $this->assertSame(0.0, $horas[12]);
    }

    public function test_asistencia_sin_egreso_se_estima_en_una_hora(): void
    {
        $s = $this->socio();
        Asistencia::create(['socio_id' => $s->id, 'fecha' => today()->subWeek(), 'hora_ingreso' => '18:00:00']);

        $horas = app(AforoService::class)->horasPico(today()->dayOfWeek);

        $this->assertGreaterThan(0, $horas[18]);
        $this->assertSame(0.0, $horas[19]);
    }

    public function test_el_socio_ve_el_aforo_en_su_portal(): void
    {
        Configuracion::guardar('capacidad_maxima', '30');
        $this->presentes(3);
        $s = $this->socio();

        $this->get('/socio/aforo')->assertRedirect('/socio/login');
        $this->withSession(['socio_id' => $s->id])->get('/socio/aforo')
            ->assertOk()->assertSee('de 30 personas')->assertSee('Horas pico');
        $this->withSession(['socio_id' => $s->id])->get('/socio')->assertOk()->assertSee('Ahora en el gimnasio');
    }

    public function test_solo_el_administrador_configura_la_capacidad(): void
    {
        $this->actingAs(User::factory()->create())->get('/configuracion')->assertForbidden();

        $admin = User::factory()->administrador()->create();
        $this->actingAs($admin)->get('/configuracion')->assertOk();
        $this->actingAs($admin)->put('/configuracion', ['capacidad_maxima' => 0])->assertSessionHasErrors('capacidad_maxima');
        $this->actingAs($admin)->put('/configuracion', ['capacidad_maxima' => 80])->assertRedirect('/configuracion');

        $this->assertSame(80, app(AforoService::class)->capacidad());
    }
}
