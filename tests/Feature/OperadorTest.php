<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** HU10 y HU30: cuentas del personal, contraseñas con bcrypt y cambio de la propia contraseña. */
class OperadorTest extends TestCase
{
    use RefreshDatabase;

    protected function sesionAbierta(User $u, string $id): void
    {
        DB::table('sessions')->insert(['id' => $id, 'user_id' => $u->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'test',
            'payload' => '', 'last_activity' => time()]);
    }

    public function test_el_administrador_crea_operadores_con_contrasena_en_bcrypt(): void
    {
        $admin = User::factory()->administrador()->create();

        $this->actingAs($admin)->post('/operadores', ['name' => 'Rocío', 'email' => 'rocio@gym.test', 'password' => 'Mostrador24',
            'password_confirmation' => 'Mostrador24', 'rol' => User::ROL_PROFESOR])->assertRedirect('/operadores');

        $rocio = User::where('email', 'rocio@gym.test')->first();
        $this->assertSame(User::ROL_PROFESOR, $rocio->rol);
        $this->assertTrue($rocio->activo);
        $this->assertNotSame('Mostrador24', $rocio->getRawOriginal('password'));
        $this->assertSame('bcrypt', password_get_info($rocio->getRawOriginal('password'))['algoName']);
        $this->assertTrue(Hash::check('Mostrador24', $rocio->password));
    }

    public function test_la_contrasena_debe_tener_letras_y_numeros(): void
    {
        $admin = User::factory()->administrador()->create();

        $this->actingAs($admin)->post('/operadores', ['name' => 'Rocío', 'email' => 'rocio@gym.test', 'password' => 'solamente',
            'password_confirmation' => 'solamente', 'rol' => User::ROL_RECEPCION])->assertSessionHasErrors('password');
        $this->actingAs($admin)->post('/operadores', ['name' => 'Rocío', 'email' => 'rocio@gym.test', 'password' => '12345678',
            'password_confirmation' => '12345678', 'rol' => User::ROL_RECEPCION])->assertSessionHasErrors('password');
    }

    public function test_solo_el_administrador_gestiona_operadores(): void
    {
        $this->actingAs(User::factory()->create())->get('/operadores')->assertForbidden();
        $this->actingAs(User::factory()->create(['rol' => User::ROL_PROFESOR]))->get('/operadores/create')->assertForbidden();
    }

    public function test_restablecer_la_contrasena_cierra_las_sesiones_del_operador(): void
    {
        config(['session.driver' => 'database']);
        $admin = User::factory()->administrador()->create();
        $recep = User::factory()->create(['password' => 'Anterior123']);
        $this->sesionAbierta($recep, 'sesion-de-recepcion');

        $this->actingAs($admin)->put("/operadores/{$recep->id}", ['name' => $recep->name, 'email' => $recep->email, 'rol' => User::ROL_RECEPCION,
            'activo' => '1', 'password' => 'Nueva12345', 'password_confirmation' => 'Nueva12345'])->assertRedirect('/operadores');

        $recep->refresh();
        $this->assertTrue(Hash::check('Nueva12345', $recep->password));
        $this->assertDatabaseMissing('sessions', ['id' => 'sesion-de-recepcion']);
    }

    public function test_editar_sin_contrasena_no_la_cambia_ni_cierra_sesiones(): void
    {
        config(['session.driver' => 'database']);
        $admin = User::factory()->administrador()->create();
        $recep = User::factory()->create(['password' => 'Anterior123']);
        $this->sesionAbierta($recep, 'sesion-de-recepcion');

        $this->actingAs($admin)->put("/operadores/{$recep->id}", ['name' => 'Otro nombre', 'email' => $recep->email, 'rol' => User::ROL_RECEPCION, 'activo' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('Anterior123', $recep->fresh()->password));
        $this->assertDatabaseHas('sessions', ['id' => 'sesion-de-recepcion']);
    }

    public function test_desactivar_un_operador_le_impide_entrar(): void
    {
        $admin = User::factory()->administrador()->create();
        $recep = User::factory()->create(['password' => 'Anterior123']);

        $this->actingAs($admin)->put("/operadores/{$recep->id}", ['name' => $recep->name, 'email' => $recep->email, 'rol' => User::ROL_RECEPCION]);
        $this->assertFalse($recep->fresh()->activo);

        auth()->logout();
        $this->post('/login', ['email' => $recep->email, 'password' => 'Anterior123'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_el_administrador_no_puede_quitarse_el_rol_ni_desactivarse(): void
    {
        $admin = User::factory()->administrador()->create();

        $this->actingAs($admin)->put("/operadores/{$admin->id}", ['name' => $admin->name, 'email' => $admin->email, 'rol' => User::ROL_RECEPCION]);

        $this->assertTrue($admin->fresh()->esAdministrador());
        $this->assertTrue($admin->fresh()->activo);
    }

    public function test_cada_operador_cambia_su_contrasena_con_la_actual(): void
    {
        config(['session.driver' => 'database']);
        $profe = User::factory()->create(['rol' => User::ROL_PROFESOR, 'password' => 'Anterior123']);
        $this->sesionAbierta($profe, 'sesion-en-otra-pc');

        $this->actingAs($profe)->get('/mi-clave')->assertOk()->assertSee('Contraseña actual');
        $this->actingAs($profe)->put('/mi-clave', ['clave_actual' => 'equivocada', 'password' => 'Nueva12345', 'password_confirmation' => 'Nueva12345'])
            ->assertSessionHasErrors('clave_actual');
        $this->actingAs($profe)->put('/mi-clave', ['clave_actual' => 'Anterior123', 'password' => 'Anterior123', 'password_confirmation' => 'Anterior123'])
            ->assertSessionHasErrors('password');

        $this->actingAs($profe)->put('/mi-clave', ['clave_actual' => 'Anterior123', 'password' => 'Nueva12345', 'password_confirmation' => 'Nueva12345'])
            ->assertRedirect('/panel');

        $this->assertTrue(Hash::check('Nueva12345', $profe->fresh()->password));
        $this->assertDatabaseMissing('sessions', ['id' => 'sesion-en-otra-pc']);
        $this->assertAuthenticatedAs($profe);
    }

    public function test_comando_restablece_la_contrasena_sin_mostrarla(): void
    {
        $admin = User::factory()->administrador()->create(['email' => 'admin@gym.test', 'activo' => false]);

        $this->artisan('gym:clave-operador', ['email' => 'admin@gym.test'])
            ->expectsQuestion('Nueva contraseña', 'Fenix2026')
            ->expectsQuestion('Repetir la contraseña', 'Fenix2026')
            ->assertSuccessful();

        $this->assertTrue(Hash::check('Fenix2026', $admin->fresh()->password));
        $this->assertTrue($admin->fresh()->activo);

        $this->artisan('gym:clave-operador', ['email' => 'admin@gym.test'])
            ->expectsQuestion('Nueva contraseña', 'corta')
            ->expectsQuestion('Repetir la contraseña', 'corta')
            ->assertFailed();
        $this->artisan('gym:clave-operador', ['email' => 'nadie@gym.test'])->assertFailed();
    }

    public function test_el_seeder_no_pisa_la_contrasena_cambiada_del_administrador(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('rol', User::ROL_ADMIN)->first();
        $admin->update(['password' => 'Cambiada123']);

        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1, User::where('rol', User::ROL_ADMIN)->count());
        $this->assertTrue(Hash::check('Cambiada123', $admin->fresh()->password));
    }

    public function test_en_produccion_sin_admin_password_no_se_crea_un_administrador_con_clave_conocida(): void
    {
        $this->app['env'] = 'production';
        $anterior = [$_ENV['ADMIN_PASSWORD'] ?? null, $_SERVER['ADMIN_PASSWORD'] ?? null];
        $_ENV['ADMIN_PASSWORD'] = $_SERVER['ADMIN_PASSWORD'] = '';

        try {
            // Se llama al seeder directamente: `db:seed` en producción pide confirmación.
            $this->app->make(DatabaseSeeder::class)->__invoke();
        } finally {
            [$_ENV['ADMIN_PASSWORD'], $_SERVER['ADMIN_PASSWORD']] = $anterior;
        }

        $this->assertSame(0, User::where('rol', User::ROL_ADMIN)->count());
    }
}
