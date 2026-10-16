<?php

namespace Tests\Feature;

use App\Models\AptoFisico;
use App\Models\Asistencia;
use App\Models\Membresia;
use App\Models\Socio;
use App\Models\User;
use App\Services\PagoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortalHistorialTest extends TestCase
{
    use RefreshDatabase;

    protected User $operador;

    protected Membresia $plan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->operador = User::factory()->create(['name' => 'María Recepción']);
        $this->plan = Membresia::create(['nombre' => 'Media', 'precio' => 35000, 'duracion_dias' => 30, 'horas_diarias_max' => 4, 'estado' => 'activa']);
    }

    protected function socio(string $nombre = 'Ana'): Socio
    {
        return Socio::create(['membresia_id' => $this->plan->id, 'user_id' => $this->operador->id, 'nombre' => $nombre, 'apellido' => 'Test',
            'dni' => (string) random_int(20000000, 49999999), 'estado' => 'activo']);
    }

    public function test_las_secciones_exigen_sesion_del_socio(): void
    {
        foreach (['/socio/asistencias', '/socio/pagos', '/socio/apto'] as $ruta) {
            $this->get($ruta)->assertRedirect('/socio/login');
        }
    }

    public function test_historial_de_asistencias_filtra_por_fechas_y_suma_horas(): void
    {
        $s = $this->socio();
        Asistencia::create(['socio_id' => $s->id, 'fecha' => '2026-09-10', 'hora_ingreso' => '08:00:00', 'hora_salida' => '09:00:00', 'tiempo_permanencia' => '01:00:00']);
        Asistencia::create(['socio_id' => $s->id, 'fecha' => '2026-10-05', 'hora_ingreso' => '18:00:00', 'hora_salida' => '19:30:00', 'tiempo_permanencia' => '01:30:00']);
        Asistencia::create(['socio_id' => $this->socio('Luis')->id, 'fecha' => '2026-10-06', 'hora_ingreso' => '07:15:00']);

        $this->withSession(['socio_id' => $s->id])->get('/socio/asistencias')
            ->assertOk()->assertSee('10/09/2026')->assertSee('05/10/2026')->assertDontSee('07:15')->assertSee('2.5 h en total');

        $this->withSession(['socio_id' => $s->id])->get('/socio/asistencias?desde=2026-10-01&hasta=2026-10-31')
            ->assertOk()->assertDontSee('10/09/2026')->assertSee('05/10/2026')->assertSee('1 asistencia');

        $this->withSession(['socio_id' => $s->id])->get('/socio/asistencias?desde=2026-10-31&hasta=2026-10-01')
            ->assertSessionHasErrors('hasta');
    }

    public function test_el_socio_ve_sus_pagos_y_descarga_su_comprobante(): void
    {
        $s = $this->socio();
        $pago = app(PagoService::class)->registrar($s, $this->plan, 'transferencia', $this->operador);

        $this->withSession(['socio_id' => $s->id])->get('/socio/pagos')
            ->assertOk()->assertSee('Media')->assertSee('$ 35.000,00')->assertSee('Transferencia')->assertSee('Cuota al día');

        $r = $this->withSession(['socio_id' => $s->id])->get("/socio/pagos/{$pago->id}/comprobante");
        $r->assertOk();
        $this->assertSame('application/pdf', $r->headers->get('content-type'));
    }

    public function test_no_puede_ver_el_comprobante_de_otro_socio(): void
    {
        $ajeno = app(PagoService::class)->registrar($this->socio('Luis'), $this->plan, 'efectivo', $this->operador);

        $this->withSession(['socio_id' => $this->socio()->id])->get("/socio/pagos/{$ajeno->id}/comprobante")->assertNotFound();
    }

    public function test_constancias_medicas_con_descarga_solo_del_propio_archivo(): void
    {
        Storage::fake('certificados');
        $s = $this->socio();
        $ruta = UploadedFile::fake()->create('apto.pdf', 10, 'application/pdf')->store("socio-{$s->id}", 'certificados');
        $apto = AptoFisico::create(['socio_id' => $s->id, 'user_id' => $this->operador->id, 'fecha_emision' => today()->subMonth(),
            'fecha_vencimiento' => today()->addMonths(6), 'estado' => 'vigente', 'archivo' => $ruta]);
        $otro = $this->socio('Luis');

        $this->withSession(['socio_id' => $s->id])->get('/socio/apto')
            ->assertOk()->assertSee('vigente hasta el '.today()->addMonths(6)->format('d/m/Y'))->assertSee('Ver archivo');
        $this->withSession(['socio_id' => $s->id])->get("/socio/apto/{$apto->id}/archivo")->assertOk();
        $this->withSession(['socio_id' => $otro->id])->get("/socio/apto/{$apto->id}/archivo")->assertNotFound();
        $this->withSession(['socio_id' => $otro->id])->get('/socio/apto')->assertOk()->assertSee('No tenés un apto físico vigente');
    }
}
