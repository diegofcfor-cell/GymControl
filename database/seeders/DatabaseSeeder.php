<?php

namespace Database\Seeders;

use App\Models\AptoFisico;
use App\Models\Asistencia;
use App\Models\Configuracion;
use App\Models\Membresia;
use App\Models\PreguntaFrecuente;
use App\Models\Socio;
use App\Models\SolicitudRutina;
use App\Models\User;
use App\Services\PagoService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Datos base (siempre): membresías y administrador inicial (si no existe).
     * Datos de demostración (solo si SEED_DEMO=true): operadores (recepción, profesor y terminal), socios, pagos, aptos y asistencias.
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

        $this->administradorInicial();

        if (filter_var(env('SEED_DEMO', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->demo();
        }
    }

    /**
     * Crea el primer administrador solo si todavía no existe; si ya existe no se toca, así que una contraseña
     * cambiada desde el panel no vuelve a la de la variable en el próximo deploy. En producción la contraseña
     * sale solo de ADMIN_PASSWORD (cargada en Render, nunca en el repositorio): sin ella no se crea nadie.
     */
    protected function administradorInicial(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@gymcontrol.test');
        $clave = env('ADMIN_PASSWORD') ?: (app()->isProduction() ? null : 'Admin1234!');

        if (User::where('email', $email)->exists()) {
            return;
        }
        if ($clave === null) {
            $this->command?->warn('No se creó el administrador inicial: falta la variable ADMIN_PASSWORD.');

            return;
        }

        User::create(['email' => $email, 'name' => env('ADMIN_NAME', 'Administrador'), 'password' => $clave, 'rol' => User::ROL_ADMIN, 'activo' => true]);
    }

    protected function demo(): void
    {
        $recep = User::firstOrCreate(
            ['email' => 'recepcion@gymcontrol.test'],
            ['name' => 'María Recepción', 'password' => 'Recepcion1234!', 'rol' => User::ROL_RECEPCION, 'activo' => true]
        );
        User::firstOrCreate(
            ['email' => 'profesor@gymcontrol.test'],
            ['name' => 'Pablo Profesor', 'password' => 'Profesor1234!', 'rol' => User::ROL_PROFESOR, 'activo' => true]
        );
        User::firstOrCreate(
            ['email' => 'terminal@gymcontrol.test'],
            ['name' => 'Tablet de la entrada', 'password' => 'Terminal1234!', 'rol' => User::ROL_TERMINAL, 'activo' => true]
        );

        $preguntas = [
            ['¿Cuál es el horario del gimnasio?', 'De lunes a viernes de 7 a 23 h y los sábados de 8 a 13 h. Domingos y feriados cerrado.', 'horario, hora, abren, cierran, abierto, sabado, domingo, feriado'],
            ['¿Qué necesito para empezar?', 'Presentarte en recepción con tu DNI y un apto físico vigente, elegir un plan y abonar la primera cuota.', 'empezar, inscribirme, anotarme, requisitos, alta, apto, certificado'],
            ['¿Cómo pago la cuota?', 'En recepción, en efectivo, débito o transferencia. La cuota se renueva desde la fecha de vencimiento.', 'pagar, pago, efectivo, transferencia, tarjeta, debito, renovar'],
            ['¿Hay profesores o clases?', 'Hay profesores en sala en todo el horario. Consultá en recepción la grilla de clases grupales.', 'profesor, profe, instructor, clases, rutina, entrenador'],
            ['¿Tienen vestuarios y duchas?', 'Sí, hay vestuarios con duchas y lockers. Traé tu candado.', 'vestuario, ducha, locker, banos, candado'],
        ];
        foreach ($preguntas as $i => [$pregunta, $respuesta, $claves]) {
            PreguntaFrecuente::firstOrCreate(['pregunta' => $pregunta], ['respuesta' => $respuesta, 'palabras_clave' => $claves, 'orden' => $i + 1, 'activa' => true]);
        }

        $web = [
            'descripcion' => 'Gimnasio de musculación y entrenamiento funcional en Formosa, con profesores en sala y aforo en vivo.',
            'servicios' => "Musculación\nEntrenamiento funcional\nCardio\nRutinas personalizadas\nVestuarios con duchas",
            'horarios' => "Lunes a viernes: 7 a 23 h\nSábados: 8 a 13 h\nDomingos y feriados: cerrado",
            'direccion' => 'Av. Ejemplo 123, Formosa',
            'telefono' => '370 400-0000',
        ];
        foreach ($web as $clave => $valor) {
            if (Configuracion::obtener($clave) === null) {
                Configuracion::guardar($clave, $valor);
            }
        }

        $demo = [
            ['Juan', 'Pérez', '30111222', 'Básica', 0, true, '111111'],
            ['Lucía', 'Gómez', '32555666', 'Media', 0, true, '222222'],
            ['Carlos', 'Romero', '28444333', 'Premium', 0, true, '333333'],
            ['Ana', 'Benítez', '35222111', 'Media', 45, true, '444444'],   // cuota vencida
            ['Martín', 'Acosta', '31999888', 'Básica', 0, false, '555555'], // sin apto físico
        ];

        // Si la base ya tiene socios (no se borró entre deploys), solo se dejan los PIN de demostración como están documentados.
        if (Socio::count() > 0) {
            foreach ($demo as [, , $dni, , , , $pin]) {
                if (! Socio::withTrashed()->where('pin', $pin)->where('dni', '!=', $dni)->exists()) {
                    Socio::withTrashed()->where('dni', $dni)->update(['pin' => $pin]);
                }
            }

            return;
        }

        $pagos = app(PagoService::class);
        $planes = Membresia::orderBy('precio')->get()->keyBy('nombre');
        foreach ($demo as [$nombre, $apellido, $dni, $plan, $diasAtras, $conApto, $pin]) {
            $socio = Socio::create([
                'membresia_id' => $planes[$plan]->id, 'user_id' => $recep->id, 'nombre' => $nombre, 'apellido' => $apellido,
                'dni' => $dni, 'pin' => $pin, 'telefono' => '37045'.substr($dni, -5), 'estado' => 'activo', 'fecha_alta' => today()->subDays(60),
                'consentimiento_datos_at' => today()->subDays(60),
            ]);
            $pagos->registrar($socio, $planes[$plan], 'efectivo', $recep, today()->subDays($diasAtras ?: 5));
            if ($conApto) {
                AptoFisico::create(['socio_id' => $socio->id, 'user_id' => $recep->id, 'fecha_emision' => today()->subMonths(2),
                    'fecha_vencimiento' => today()->addMonths(10), 'estado' => 'vigente']);
            }
        }

        $this->asistenciasDemo();

        SolicitudRutina::create(['socio_id' => Socio::where('dni', '30111222')->value('id'), 'objetivo' => 'Ganar masa muscular',
            'dias_por_semana' => 3, 'nivel' => 'principiante', 'comentarios' => 'Tuve una molestia en el hombro derecho.', 'estado' => 'pendiente']);
    }

    /**
     * Cuatro semanas de ingresos con horarios típicos de gimnasio (mañana temprano y
     * después del trabajo), para que el gráfico semanal de aforo tenga datos.
     */
    protected function asistenciasDemo(): void
    {
        // [DNI, días de la semana (0 = domingo), hora de ingreso, minutos]
        $habitos = [
            ['30111222', [1, 3, 5], '19:00', 90],
            ['32555666', [2, 4, 6], '08:00', 75],
            ['28444333', [1, 2, 3, 4, 5], '18:30', 90],
            ['35222111', [1, 3], '19:30', 60],
            ['31999888', [2, 4], '07:00', 60],
        ];
        foreach ($habitos as [$dni, $dias, $hora, $minutos]) {
            $socioId = Socio::where('dni', $dni)->value('id');
            for ($d = 1; $d <= 28; $d++) {
                $fecha = today()->subDays($d);
                if (! in_array($fecha->dayOfWeek, $dias, true)) {
                    continue;
                }
                $ingreso = $fecha->copy()->setTimeFromTimeString($hora)->addMinutes(($d * 7) % 25); // variación de unos minutos
                $salida = $ingreso->copy()->addMinutes($minutos);
                Asistencia::create(['socio_id' => $socioId, 'fecha' => $fecha->toDateString(), 'hora_ingreso' => $ingreso->format('H:i:s'),
                    'hora_salida' => $salida->format('H:i:s'), 'tiempo_permanencia' => sprintf('%02d:%02d:00', intdiv($minutos, 60), $minutos % 60)]);
            }
        }
    }
}
