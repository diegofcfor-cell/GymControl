<?php

namespace Database\Seeders;

use App\Models\AptoFisico;
use App\Models\Asistencia;
use App\Models\Membresia;
use App\Models\Socio;
use App\Models\User;
use App\Services\PagoService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Datos base (siempre): membresías y administrador inicial.
     * Datos de demostración (solo si SEED_DEMO=true): operador, socios, pagos, aptos y asistencias.
     */
    public function run(): void
    {
        $planes = [
            ['nombre' => 'Básica', 'precio' => 25000, 'duracion_dias' => 30, 'horas_diarias_max' => 2],
            ['nombre' => 'Media', 'precio' => 35000, 'duracion_dias' => 30, 'horas_diarias_max' => 4],
            ['nombre' => 'Premium', 'precio' => 50000, 'duracion_dias' => 30, 'horas_diarias_max' => null], // pase libre
        ];
        foreach ($planes as $p) {
            Membresia::firstOrCreate(['nombre' => $p['nombre']], $p + ['estado' => 'activa']);
        }

        $admin = User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@gymcontrol.test')],
            ['name' => env('ADMIN_NAME', 'Administrador'), 'password' => env('ADMIN_PASSWORD', 'Admin1234!'), 'rol' => User::ROL_ADMIN, 'activo' => true]
        );

        if (filter_var(env('SEED_DEMO', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->demo($admin);
        }
    }

    protected function demo(User $admin): void
    {
        $recep = User::firstOrCreate(
            ['email' => 'recepcion@gymcontrol.test'],
            ['name' => 'María Recepción', 'password' => 'Recepcion1234!', 'rol' => User::ROL_RECEPCION, 'activo' => true]
        );
        if (Socio::count() > 0) {
            return;
        }

        $pagos = app(PagoService::class);
        $planes = Membresia::orderBy('precio')->get()->keyBy('nombre');
        $demo = [
            ['Juan', 'Pérez', '30111222', 'Básica', 0, true],
            ['Lucía', 'Gómez', '32555666', 'Media', 0, true],
            ['Carlos', 'Romero', '28444333', 'Premium', 0, true],
            ['Ana', 'Benítez', '35222111', 'Media', 45, true],   // cuota vencida
            ['Martín', 'Acosta', '31999888', 'Básica', 0, false], // sin apto físico
        ];
        foreach ($demo as [$nombre, $apellido, $dni, $plan, $diasAtras, $conApto]) {
            $socio = Socio::create([
                'membresia_id' => $planes[$plan]->id, 'user_id' => $recep->id, 'nombre' => $nombre, 'apellido' => $apellido,
                'dni' => $dni, 'telefono' => '37045'.substr($dni, -5), 'estado' => 'activo', 'fecha_alta' => today()->subDays(60),
            ]);
            $pagos->registrar($socio, $planes[$plan], 'efectivo', $recep, today()->subDays($diasAtras ?: 5));
            if ($conApto) {
                AptoFisico::create(['socio_id' => $socio->id, 'user_id' => $recep->id, 'fecha_emision' => today()->subMonths(2),
                    'fecha_vencimiento' => today()->addMonths(10), 'estado' => 'vigente']);
            }
            if ($diasAtras === 0 && $conApto) {
                foreach ([1, 2, 3] as $d) {
                    Asistencia::create(['socio_id' => $socio->id, 'fecha' => today()->subDays($d)->toDateString(),
                        'hora_ingreso' => '18:00:00', 'hora_salida' => '19:30:00', 'tiempo_permanencia' => '01:30:00']);
                }
            }
        }
    }
}
