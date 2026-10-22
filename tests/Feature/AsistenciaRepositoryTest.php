<?php

namespace Tests\Feature;

use App\Models\Asistencia;
use App\Models\Membresia;
use App\Models\Socio;
use App\Models\User;
use App\Repositories\AsistenciaRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** HU23: la regla "presente = asistencia de hoy sin egreso" vive en un solo lugar. */
class AsistenciaRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected function socio(string $nombre): Socio
    {
        $plan = Membresia::create(['nombre' => 'Plan'.uniqid(), 'precio' => 1000, 'duracion_dias' => 30, 'estado' => 'activa']);

        return Socio::create([
            'membresia_id' => $plan->id, 'user_id' => User::factory()->create()->id, 'nombre' => $nombre, 'apellido' => 'Test',
            'dni' => (string) random_int(20000000, 49999999), 'estado' => 'activo',
        ]);
    }

    public function test_solo_cuenta_las_asistencias_de_hoy_sin_egreso(): void
    {
        $tarde = $this->socio('Tarde');
        $temprano = $this->socio('Temprano');
        $salio = $this->socio('Salió');
        $ayer = $this->socio('Ayer');

        Asistencia::create(['socio_id' => $tarde->id, 'fecha' => today(), 'hora_ingreso' => '10:00:00']);
        Asistencia::create(['socio_id' => $temprano->id, 'fecha' => today(), 'hora_ingreso' => '08:00:00']);
        Asistencia::create(['socio_id' => $salio->id, 'fecha' => today(), 'hora_ingreso' => '07:00:00', 'hora_salida' => '08:00:00']);
        Asistencia::create(['socio_id' => $ayer->id, 'fecha' => today()->subDay(), 'hora_ingreso' => '20:00:00']); // olvidó marcar la salida

        $repositorio = new AsistenciaRepository;

        $this->assertSame(2, $repositorio->cantidadPresentes());
        $this->assertSame(['Temprano', 'Tarde'], $repositorio->presentesHoy()->pluck('socio.nombre')->all());
        $this->assertTrue($repositorio->estaDentro($tarde));
        $this->assertFalse($repositorio->estaDentro($salio));
        $this->assertNull($repositorio->abiertaDeHoy($ayer));
        $this->assertSame('10:00:00', $repositorio->abiertaDeHoy($tarde)->hora_ingreso);
    }
}
