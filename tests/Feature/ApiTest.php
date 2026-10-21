<?php

namespace Tests\Feature;

use App\Models\AptoFisico;
use App\Models\Asistencia;
use App\Models\Membresia;
use App\Models\Socio;
use App\Models\User;
use App\Services\AutenticacionSocioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
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

    protected function token(Socio $socio): string
    {
        return $this->postJson('/api/socio/login', ['dni' => $socio->dni, 'pin' => $socio->pin])
            ->assertCreated()
            ->json('token');
    }

    public function test_aforo_es_publico_y_no_expone_datos_personales(): void
    {
        $s = $this->socio();
        Asistencia::create(['socio_id' => $s->id, 'fecha' => today(), 'hora_ingreso' => now()->format('H:i:s')]);

        $this->getJson('/api/aforo')
            ->assertOk()
            ->assertJsonPath('data.presentes', 1)
            ->assertJsonStructure(['data' => ['presentes', 'capacidad', 'porcentaje', 'nivel', 'franjas_mas_concurridas', 'actualizado']])
            ->assertDontSee('Ana');
    }

    public function test_login_devuelve_token_bearer(): void
    {
        $s = $this->socio();

        $this->postJson('/api/socio/login', ['dni' => $s->dni, 'pin' => $s->pin])
            ->assertCreated()
            ->assertJsonStructure(['token', 'tipo', 'vence'])
            ->assertJsonPath('tipo', 'Bearer');
    }

    public function test_login_con_pin_incorrecto_responde_401(): void
    {
        $s = $this->socio();
        $otro = $s->pin === '000000' ? '111111' : '000000';

        $this->postJson('/api/socio/login', ['dni' => $s->dni, 'pin' => $otro])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'DNI o PIN incorrectos.');
    }

    public function test_login_valida_los_datos(): void
    {
        $this->postJson('/api/socio/login', ['dni' => '', 'pin' => '12'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['dni', 'pin']);
    }

    public function test_login_se_bloquea_tras_varios_intentos_fallidos(): void
    {
        $s = $this->socio();
        $otro = $s->pin === '000000' ? '111111' : '000000';

        for ($i = 0; $i < AutenticacionSocioService::MAX_INTENTOS; $i++) {
            $this->postJson('/api/socio/login', ['dni' => $s->dni, 'pin' => $otro])->assertUnauthorized();
        }

        $this->postJson('/api/socio/login', ['dni' => $s->dni, 'pin' => $s->pin])
            ->assertUnauthorized()
            ->assertJsonFragment(['message' => 'Demasiados intentos. Probá de nuevo en 15 min.']);
    }

    public function test_socio_inactivo_no_obtiene_token(): void
    {
        $s = $this->socio(['estado' => 'inactivo']);

        $this->postJson('/api/socio/login', ['dni' => $s->dni, 'pin' => $s->pin])->assertUnauthorized();
    }

    public function test_sin_token_responde_401_en_json(): void
    {
        $this->get('/api/socio/perfil')->assertUnauthorized()->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_perfil_muestra_los_datos_propios_sin_pin(): void
    {
        $s = $this->socio();
        AptoFisico::create(['socio_id' => $s->id, 'user_id' => $s->user_id, 'fecha_emision' => today()->subMonth(),
            'fecha_vencimiento' => today()->addMonths(6), 'estado' => 'vigente']);

        $this->withToken($this->token($s))
            ->getJson('/api/socio/perfil')
            ->assertOk()
            ->assertJsonPath('data.nombre', 'Ana')
            ->assertJsonPath('data.cuota_al_dia', true)
            ->assertJsonPath('data.apto_vigente', true)
            ->assertJsonMissingPath('data.pin')
            ->assertDontSee($s->qr_codigo);
    }

    public function test_asistencias_solo_del_socio_del_token(): void
    {
        $s = $this->socio();
        $otro = $this->socio(['dni' => '40999888', 'nombre' => 'Otro']);
        Asistencia::create(['socio_id' => $s->id, 'fecha' => today(), 'hora_ingreso' => '08:00:00',
            'hora_salida' => '09:30:00', 'tiempo_permanencia' => '01:30:00']);
        Asistencia::create(['socio_id' => $otro->id, 'fecha' => today(), 'hora_ingreso' => '10:00:00']);

        $this->withToken($this->token($s))
            ->getJson('/api/socio/asistencias')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.minutos_permanencia', 90)
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_logout_revoca_el_token(): void
    {
        $s = $this->socio();
        $token = $this->token($s);

        $this->withToken($token)->postJson('/api/socio/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_token_de_socio_dado_de_baja_deja_de_servir(): void
    {
        $s = $this->socio();
        $token = $this->token($s);
        $s->update(['estado' => 'inactivo']);

        $this->withToken($token)->getJson('/api/socio/perfil')->assertForbidden();
    }
}
