<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Biblioteca de rutinas semanales base para el profesor.
 *
 * Viven en código (no en la base) para que estén siempre disponibles, aunque la
 * base se reinicie. El profesor parte de una y la ajusta al socio antes de guardarla.
 * Cada ejercicio es [ejercicio, series, repeticiones, descanso, notas].
 */
class PlantillasRutina
{
    public static function todas(): Collection
    {
        return collect(self::DATOS)->map(fn ($p, $codigo) => $p + ['codigo' => $codigo]);
    }

    public static function buscar(?string $codigo): ?array
    {
        return $codigo ? self::todas()->get(strtoupper($codigo)) : null;
    }

    /** Ordena la biblioteca poniendo primero las que mejor encajan con el pedido del socio. */
    public static function sugeridas(?string $objetivo, ?int $dias, ?string $nivel): Collection
    {
        return self::todas()->sortByDesc(fn ($p) => ($p['objetivo'] === $objetivo ? 100 : 0)
            + ($p['nivel'] === $nivel ? 10 : 0)
            - ($dias ? abs($p['dias'] - $dias) : 0));
    }

    /** Filas listas para el formulario de rutina. */
    public static function filas(array $plantilla): array
    {
        $filas = [];
        foreach ($plantilla['semana'] as $dia => $ejercicios) {
            foreach ($ejercicios as [$ejercicio, $series, $reps, $descanso, $notas]) {
                $filas[] = ['dia' => $dia, 'ejercicio' => $ejercicio, 'series' => $series, 'repeticiones' => $reps, 'descanso' => $descanso, 'notas' => $notas];
            }
        }

        return $filas;
    }

    public static function cantidadEjercicios(array $plantilla): int
    {
        return array_sum(array_map('count', $plantilla['semana']));
    }

    protected const CALOR = 'Entrada en calor: 8-10 min de cardio suave y movilidad articular. Al final, 5 min de estiramientos.';

    protected const DATOS = [
        // ── Ganar masa muscular ──────────────────────────────────────────
        'R01' => [
            'nombre' => 'R01 · Masa muscular · Full body inicial', 'objetivo' => 'Ganar masa muscular', 'nivel' => 'principiante', 'dias' => 3,
            'resumen' => 'Todo el cuerpo 3 veces por semana con básicos en máquina y mancuernas. Ideal para aprender la técnica.',
            'observaciones' => self::CALOR."\nUsar un peso que permita terminar cada serie con 2 repeticiones en reserva. Subir el peso cuando se completen todas las series con buena técnica. Dejar un día de descanso entre sesiones.",
            'semana' => [
                'Día 1 · Full body A' => [
                    ['Prensa de piernas', 3, '10-12', '90 s', ''],
                    ['Press de pecho en máquina', 3, '10-12', '90 s', ''],
                    ['Jalón al pecho', 3, '10-12', '90 s', 'Agarre al ancho de hombros'],
                    ['Press de hombros con mancuernas', 2, '10-12', '60 s', 'Sentado, espalda apoyada'],
                    ['Curl femoral en máquina', 2, '12', '60 s', ''],
                    ['Plancha abdominal', 3, '30 s', '45 s', ''],
                ],
                'Día 2 · Full body B' => [
                    ['Sentadilla goblet', 3, '10-12', '90 s', 'Mancuerna pegada al pecho'],
                    ['Remo sentado en polea', 3, '10-12', '90 s', ''],
                    ['Press inclinado con mancuernas', 3, '10-12', '90 s', ''],
                    ['Elevaciones laterales', 2, '12-15', '60 s', ''],
                    ['Curl de bíceps con mancuernas', 2, '12', '60 s', ''],
                    ['Extensión de tríceps en polea', 2, '12', '60 s', ''],
                ],
                'Día 3 · Full body C' => [
                    ['Peso muerto rumano con mancuernas', 3, '10-12', '90 s', 'Espalda neutra, cadera atrás'],
                    ['Press de pecho con mancuernas', 3, '10-12', '90 s', ''],
                    ['Remo con mancuerna a una mano', 3, '10-12', '60 s', 'Por lado'],
                    ['Estocadas alternadas', 2, '10 por pierna', '60 s', ''],
                    ['Gemelos en máquina', 3, '15', '45 s', ''],
                    ['Crunch abdominal', 3, '15', '45 s', ''],
                ],
            ],
        ],
        'R02' => [
            'nombre' => 'R02 · Masa muscular · Torso / Pierna', 'objetivo' => 'Ganar masa muscular', 'nivel' => 'intermedio', 'dias' => 4,
            'resumen' => 'Dos días de torso y dos de pierna. Cada músculo se trabaja 2 veces por semana con más volumen.',
            'observaciones' => self::CALOR."\nProgresión: cuando se completan todas las repeticiones en todas las series, subir 2,5 a 5 kg. Orden sugerido: lunes torso A, martes pierna A, jueves torso B, viernes pierna B.",
            'semana' => [
                'Día 1 · Torso A' => [
                    ['Press de banca con barra', 4, '6-8', '2 min', ''],
                    ['Remo con barra', 4, '8-10', '2 min', ''],
                    ['Press militar con mancuernas', 3, '8-10', '90 s', ''],
                    ['Jalón al pecho', 3, '10-12', '90 s', ''],
                    ['Curl de bíceps con barra', 3, '10-12', '60 s', ''],
                    ['Fondos en paralelas asistidos', 3, '10-12', '60 s', ''],
                ],
                'Día 2 · Pierna A' => [
                    ['Sentadilla con barra', 4, '6-8', '2-3 min', ''],
                    ['Peso muerto rumano', 3, '8-10', '2 min', ''],
                    ['Prensa de piernas', 3, '10-12', '90 s', ''],
                    ['Curl femoral acostado', 3, '10-12', '60 s', ''],
                    ['Gemelos de pie', 4, '12-15', '60 s', ''],
                    ['Plancha abdominal', 3, '45 s', '45 s', ''],
                ],
                'Día 3 · Torso B' => [
                    ['Press inclinado con mancuernas', 4, '8-10', '90 s', ''],
                    ['Dominadas o jalón', 4, '6-10', '2 min', 'Asistidas si hace falta'],
                    ['Remo sentado en polea', 3, '10-12', '90 s', ''],
                    ['Aperturas en máquina', 3, '12-15', '60 s', ''],
                    ['Elevaciones laterales', 3, '12-15', '60 s', ''],
                    ['Curl martillo + extensión tríceps en polea', 3, '12', '60 s', 'Superserie'],
                ],
                'Día 4 · Pierna B' => [
                    ['Peso muerto convencional', 4, '5-6', '3 min', 'Técnica antes que peso'],
                    ['Sentadilla búlgara', 3, '8-10 por pierna', '90 s', ''],
                    ['Hip thrust con barra', 3, '10-12', '90 s', ''],
                    ['Extensión de cuádriceps', 3, '12-15', '60 s', ''],
                    ['Gemelos sentado', 3, '15', '60 s', ''],
                    ['Elevación de piernas colgado', 3, '12', '60 s', ''],
                ],
            ],
        ],
        'R03' => [
            'nombre' => 'R03 · Masa muscular · Torso-Pierna + Empuje-Tirón', 'objetivo' => 'Ganar masa muscular', 'nivel' => 'avanzado', 'dias' => 5,
            'resumen' => 'Cinco días: fuerza en torso y pierna al inicio de la semana e hipertrofia por grupos al final.',
            'observaciones' => self::CALOR."\nDías 1 y 2 pesados (RIR 1-2). Días 3 a 5 con más volumen y menos descanso. Cada 6 semanas, una semana de descarga con la mitad de series.",
            'semana' => [
                'Día 1 · Torso fuerza' => [
                    ['Press de banca con barra', 5, '5', '3 min', ''],
                    ['Remo Pendlay', 5, '5', '2-3 min', ''],
                    ['Press militar de pie', 4, '6', '2 min', ''],
                    ['Dominadas lastradas', 4, '6', '2 min', ''],
                    ['Fondos en paralelas', 3, '8', '90 s', ''],
                ],
                'Día 2 · Pierna fuerza' => [
                    ['Sentadilla con barra', 5, '5', '3 min', ''],
                    ['Peso muerto rumano', 4, '6-8', '2 min', ''],
                    ['Prensa de piernas', 3, '8-10', '2 min', ''],
                    ['Curl femoral sentado', 3, '10', '90 s', ''],
                    ['Gemelos de pie', 4, '10-12', '60 s', ''],
                ],
                'Día 3 · Empuje' => [
                    ['Press inclinado con mancuernas', 4, '8-12', '90 s', ''],
                    ['Press de pecho en máquina', 3, '10-12', '90 s', ''],
                    ['Aperturas en polea', 3, '12-15', '60 s', ''],
                    ['Elevaciones laterales', 4, '12-15', '45 s', ''],
                    ['Press francés', 3, '10-12', '60 s', ''],
                    ['Extensión de tríceps en polea', 3, '12-15', '45 s', ''],
                ],
                'Día 4 · Tirón' => [
                    ['Jalón al pecho', 4, '8-12', '90 s', ''],
                    ['Remo con mancuerna a una mano', 4, '10-12', '90 s', ''],
                    ['Remo en polea baja agarre ancho', 3, '12', '60 s', ''],
                    ['Face pull', 3, '15', '45 s', ''],
                    ['Curl de bíceps con barra', 3, '10-12', '60 s', ''],
                    ['Curl martillo', 3, '12', '45 s', ''],
                ],
                'Día 5 · Pierna volumen' => [
                    ['Sentadilla hack', 4, '10-12', '2 min', ''],
                    ['Hip thrust con barra', 4, '10-12', '90 s', ''],
                    ['Estocadas caminando', 3, '12 por pierna', '90 s', ''],
                    ['Extensión de cuádriceps', 3, '15', '60 s', ''],
                    ['Curl femoral acostado', 3, '12-15', '60 s', ''],
                    ['Rueda abdominal', 3, '10-12', '60 s', ''],
                ],
            ],
        ],

        // ── Bajar de peso ────────────────────────────────────────────────
        'R04' => [
            'nombre' => 'R04 · Bajar de peso · Circuito inicial', 'objetivo' => 'Bajar de peso', 'nivel' => 'principiante', 'dias' => 3,
            'resumen' => 'Circuitos de fuerza en máquinas más cardio moderado. Mucho gasto calórico sin impacto fuerte.',
            'observaciones' => self::CALOR."\nHacer los ejercicios del circuito uno tras otro con 30 s entre ellos; al terminar la vuelta descansar 2 min. La pérdida de peso depende sobre todo de la alimentación: sugerir consulta con nutricionista.",
            'semana' => [
                'Día 1 · Circuito + cardio' => [
                    ['Prensa de piernas', 3, '15', '30 s', 'Circuito'],
                    ['Press de pecho en máquina', 3, '15', '30 s', 'Circuito'],
                    ['Jalón al pecho', 3, '15', '30 s', 'Circuito'],
                    ['Puente de glúteos', 3, '15', '30 s', 'Circuito'],
                    ['Bicicleta fija', 1, '20 min', '', 'Ritmo que permita hablar'],
                ],
                'Día 2 · Cardio + core' => [
                    ['Caminata en cinta con inclinación', 1, '25 min', '', 'Inclinación 6-10 %'],
                    ['Elíptico', 1, '10 min', '', ''],
                    ['Plancha abdominal', 3, '20-30 s', '30 s', ''],
                    ['Dead bug', 3, '10 por lado', '30 s', ''],
                ],
                'Día 3 · Circuito + cardio' => [
                    ['Sentadilla a banco', 3, '15', '30 s', 'Circuito'],
                    ['Remo sentado en polea', 3, '15', '30 s', 'Circuito'],
                    ['Press de hombros en máquina', 3, '12-15', '30 s', 'Circuito'],
                    ['Curl femoral en máquina', 3, '15', '30 s', 'Circuito'],
                    ['Remo ergómetro', 1, '10 min', '', 'Ritmo moderado'],
                    ['Bicicleta fija', 1, '10 min', '', ''],
                ],
            ],
        ],
        'R05' => [
            'nombre' => 'R05 · Bajar de peso · Fuerza + cardio', 'objetivo' => 'Bajar de peso', 'nivel' => 'intermedio', 'dias' => 4,
            'resumen' => 'Dos días de fuerza para conservar músculo y dos de cardio con intervalos.',
            'observaciones' => self::CALOR."\nEn los días de fuerza mantener pesos exigentes: es lo que conserva la masa muscular mientras se baja de peso. Apuntar a 8.000-10.000 pasos diarios fuera del gimnasio.",
            'semana' => [
                'Día 1 · Fuerza tren inferior' => [
                    ['Sentadilla con barra o goblet', 4, '10', '90 s', ''],
                    ['Peso muerto rumano con mancuernas', 3, '10-12', '90 s', ''],
                    ['Estocadas alternadas', 3, '12 por pierna', '60 s', ''],
                    ['Hip thrust', 3, '12', '60 s', ''],
                    ['Cinta caminando inclinada', 1, '15 min', '', ''],
                ],
                'Día 2 · Intervalos' => [
                    ['Bicicleta fija: 30 s fuerte / 90 s suave', 8, '2 min', '', 'Total 16 min'],
                    ['Remo ergómetro', 1, '10 min', '', 'Ritmo moderado'],
                    ['Plancha lateral', 3, '30 s por lado', '30 s', ''],
                    ['Mountain climbers', 3, '30 s', '30 s', ''],
                ],
                'Día 3 · Fuerza tren superior' => [
                    ['Press de banca con mancuernas', 4, '10', '90 s', ''],
                    ['Remo con mancuerna a una mano', 4, '10-12', '60 s', ''],
                    ['Press de hombros con mancuernas', 3, '10-12', '60 s', ''],
                    ['Jalón al pecho', 3, '12', '60 s', ''],
                    ['Elíptico', 1, '15 min', '', ''],
                ],
                'Día 4 · Cardio largo + core' => [
                    ['Cinta, bici o elíptico continuo', 1, '35-40 min', '', 'Zona 2: se puede hablar'],
                    ['Crunch en polea', 3, '15', '45 s', ''],
                    ['Bird dog', 3, '10 por lado', '30 s', ''],
                ],
            ],
        ],
        'R06' => [
            'nombre' => 'R06 · Bajar de peso · Metabólico avanzado', 'objetivo' => 'Bajar de peso', 'nivel' => 'avanzado', 'dias' => 5,
            'resumen' => 'Fuerza en superseries, HIIT y cardio largo. Alta exigencia para socios con buena base.',
            'observaciones' => self::CALOR."\nSuperseries: dos ejercicios seguidos sin descanso, luego la pausa indicada. No hacer HIIT dos días seguidos. Controlar sueño y alimentación.",
            'semana' => [
                'Día 1 · Pierna en superseries' => [
                    ['Sentadilla con barra + saltos al cajón', 4, '8 + 8', '2 min', 'Superserie'],
                    ['Peso muerto rumano + swing con pesa rusa', 4, '10 + 15', '90 s', 'Superserie'],
                    ['Estocadas caminando', 3, '12 por pierna', '60 s', ''],
                    ['Remo ergómetro', 1, '10 min', '', ''],
                ],
                'Día 2 · HIIT' => [
                    ['Air bike o bici: 20 s máximo / 40 s suave', 10, '1 min', '', ''],
                    ['Burpees', 4, '10', '60 s', ''],
                    ['Battle rope', 4, '30 s', '30 s', ''],
                    ['Plancha con toque de hombros', 3, '20', '30 s', ''],
                ],
                'Día 3 · Torso en superseries' => [
                    ['Press de banca + remo con barra', 4, '8 + 8', '2 min', 'Superserie'],
                    ['Press militar + dominadas', 4, '8 + máx.', '90 s', 'Superserie'],
                    ['Flexiones de brazos', 3, 'máx.', '60 s', ''],
                    ['Elíptico', 1, '12 min', '', ''],
                ],
                'Día 4 · Cardio largo' => [
                    ['Cinta inclinada, bici o elíptico', 1, '45 min', '', 'Zona 2'],
                    ['Rueda abdominal', 3, '10', '60 s', ''],
                ],
                'Día 5 · Full body metabólico' => [
                    ['Thruster con mancuernas', 4, '12', '60 s', ''],
                    ['Remo renegado', 4, '10 por lado', '60 s', ''],
                    ['Swing con pesa rusa', 4, '20', '60 s', ''],
                    ['Farmer walk', 4, '30 m', '60 s', 'Mancuernas pesadas'],
                    ['Remo ergómetro: 250 m fuerte', 5, '250 m', '90 s', ''],
                ],
            ],
        ],

        // ── Tonificar ────────────────────────────────────────────────────
        'R07' => [
            'nombre' => 'R07 · Tonificar · Full body 2 días', 'objetivo' => 'Tonificar', 'nivel' => 'principiante', 'dias' => 2,
            'resumen' => 'Dos sesiones completas por semana, repeticiones moderadas y foco en postura.',
            'observaciones' => self::CALOR."\nRepeticiones controladas (2 s bajando). Cuando 15 repeticiones resulten fáciles, aumentar el peso.",
            'semana' => [
                'Día 1 · Full body A' => [
                    ['Sentadilla goblet', 3, '12-15', '60 s', ''],
                    ['Press de pecho en máquina', 3, '12-15', '60 s', ''],
                    ['Remo sentado en polea', 3, '12-15', '60 s', ''],
                    ['Puente de glúteos', 3, '15', '45 s', ''],
                    ['Elevaciones laterales', 2, '15', '45 s', ''],
                    ['Plancha abdominal', 3, '30 s', '30 s', ''],
                ],
                'Día 2 · Full body B' => [
                    ['Prensa de piernas', 3, '12-15', '60 s', ''],
                    ['Jalón al pecho', 3, '12-15', '60 s', ''],
                    ['Press de hombros en máquina', 3, '12-15', '60 s', ''],
                    ['Abducción de cadera en máquina', 3, '15-20', '45 s', ''],
                    ['Curl de bíceps + extensión de tríceps', 2, '15', '45 s', 'Superserie'],
                    ['Dead bug', 3, '10 por lado', '30 s', ''],
                ],
            ],
        ],
        'R08' => [
            'nombre' => 'R08 · Tonificar · Superior / Inferior', 'objetivo' => 'Tonificar', 'nivel' => 'intermedio', 'dias' => 4,
            'resumen' => 'Cuatro días alternando tren superior e inferior, con un bloque corto de cardio al final.',
            'observaciones' => self::CALOR."\nTerminar cada sesión con 10 min de cardio moderado. Mantener técnica limpia y descansos cortos.",
            'semana' => [
                'Día 1 · Tren inferior A' => [
                    ['Sentadilla con barra', 4, '10-12', '90 s', ''],
                    ['Hip thrust', 4, '12', '60 s', ''],
                    ['Estocadas atrás', 3, '12 por pierna', '60 s', ''],
                    ['Curl femoral acostado', 3, '12-15', '45 s', ''],
                    ['Gemelos de pie', 3, '15-20', '45 s', ''],
                ],
                'Día 2 · Tren superior A' => [
                    ['Press inclinado con mancuernas', 3, '10-12', '60 s', ''],
                    ['Jalón al pecho', 3, '10-12', '60 s', ''],
                    ['Elevaciones laterales', 3, '15', '45 s', ''],
                    ['Remo con mancuerna a una mano', 3, '12', '45 s', ''],
                    ['Fondos en banco', 3, '12-15', '45 s', ''],
                    ['Crunch en polea', 3, '15', '45 s', ''],
                ],
                'Día 3 · Tren inferior B' => [
                    ['Peso muerto rumano', 4, '10-12', '90 s', ''],
                    ['Sentadilla búlgara', 3, '10 por pierna', '60 s', ''],
                    ['Patada de glúteo en polea', 3, '15 por pierna', '45 s', ''],
                    ['Abducción de cadera en máquina', 3, '20', '45 s', ''],
                    ['Extensión de cuádriceps', 3, '15', '45 s', ''],
                ],
                'Día 4 · Tren superior B' => [
                    ['Press de banca con mancuernas', 3, '10-12', '60 s', ''],
                    ['Remo sentado en polea', 3, '12', '60 s', ''],
                    ['Press Arnold', 3, '12', '60 s', ''],
                    ['Face pull', 3, '15', '45 s', ''],
                    ['Curl de bíceps + extensión de tríceps', 3, '12-15', '45 s', 'Superserie'],
                    ['Plancha lateral', 3, '30 s por lado', '30 s', ''],
                ],
            ],
        ],
        'R09' => [
            'nombre' => 'R09 · Tonificar · Glúteos, piernas y core', 'objetivo' => 'Tonificar', 'nivel' => 'intermedio', 'dias' => 3,
            'resumen' => 'Énfasis en glúteos y piernas dos días por semana, más un día de tren superior y core.',
            'observaciones' => self::CALOR."\nActivar glúteos antes de cada sesión de piernas con banda elástica (2 x 15 caminatas laterales).",
            'semana' => [
                'Día 1 · Glúteos y femorales' => [
                    ['Hip thrust con barra', 4, '10-12', '90 s', ''],
                    ['Peso muerto rumano', 4, '10-12', '90 s', ''],
                    ['Patada de glúteo en polea', 3, '15 por pierna', '45 s', ''],
                    ['Abducción de cadera en máquina', 3, '20', '45 s', ''],
                    ['Curl femoral sentado', 3, '12-15', '45 s', ''],
                ],
                'Día 2 · Tren superior y core' => [
                    ['Jalón al pecho', 3, '12', '60 s', ''],
                    ['Press de pecho con mancuernas', 3, '12', '60 s', ''],
                    ['Remo sentado en polea', 3, '12', '60 s', ''],
                    ['Elevaciones laterales', 3, '15', '45 s', ''],
                    ['Plancha abdominal', 3, '40 s', '30 s', ''],
                    ['Pallof press', 3, '12 por lado', '30 s', ''],
                ],
                'Día 3 · Cuádriceps y glúteos' => [
                    ['Sentadilla con barra', 4, '10', '90 s', ''],
                    ['Sentadilla búlgara', 3, '10 por pierna', '60 s', ''],
                    ['Prensa con pies altos', 3, '12-15', '60 s', ''],
                    ['Step up al cajón', 3, '12 por pierna', '45 s', ''],
                    ['Puente de glúteos a una pierna', 3, '12 por pierna', '45 s', ''],
                ],
            ],
        ],

        // ── Mejorar resistencia ──────────────────────────────────────────
        'R10' => [
            'nombre' => 'R10 · Resistencia · Base aeróbica', 'objetivo' => 'Mejorar resistencia', 'nivel' => 'principiante', 'dias' => 3,
            'resumen' => 'Construir la base cardiovascular con cardio continuo y fuerza-resistencia liviana.',
            'observaciones' => self::CALOR."\nCardio a intensidad que permita hablar en frases cortas. Sumar 2-3 minutos por semana al cardio continuo.",
            'semana' => [
                'Día 1 · Cardio + fuerza' => [
                    ['Cinta caminando rápido o trote suave', 1, '20 min', '', ''],
                    ['Prensa de piernas', 2, '15-20', '45 s', ''],
                    ['Remo sentado en polea', 2, '15-20', '45 s', ''],
                    ['Press de pecho en máquina', 2, '15-20', '45 s', ''],
                    ['Plancha abdominal', 2, '30 s', '30 s', ''],
                ],
                'Día 2 · Cardio continuo' => [
                    ['Bicicleta fija', 1, '15 min', '', ''],
                    ['Elíptico', 1, '15 min', '', ''],
                    ['Movilidad de cadera y hombros', 1, '5 min', '', ''],
                ],
                'Día 3 · Cardio + fuerza' => [
                    ['Remo ergómetro', 1, '10 min', '', ''],
                    ['Cinta caminando inclinada', 1, '15 min', '', ''],
                    ['Sentadilla goblet', 2, '15', '45 s', ''],
                    ['Jalón al pecho', 2, '15', '45 s', ''],
                    ['Puente de glúteos', 2, '15', '45 s', ''],
                ],
            ],
        ],
        'R11' => [
            'nombre' => 'R11 · Resistencia · Intervalos y circuitos', 'objetivo' => 'Mejorar resistencia', 'nivel' => 'intermedio', 'dias' => 4,
            'resumen' => 'Combina cardio continuo, intervalos y circuitos de fuerza-resistencia para subir el aguante.',
            'observaciones' => self::CALOR."\nNo hacer los dos días de intervalos seguidos. En los circuitos, 3 vueltas con 2 min de pausa entre vueltas.",
            'semana' => [
                'Día 1 · Intervalos en cinta' => [
                    ['Trote: 1 min rápido / 2 min suave', 8, '3 min', '', 'Total 24 min'],
                    ['Sentadilla con salto', 3, '12', '60 s', ''],
                    ['Plancha abdominal', 3, '45 s', '30 s', ''],
                ],
                'Día 2 · Circuito resistencia' => [
                    ['Swing con pesa rusa', 3, '20', '20 s', 'Circuito'],
                    ['Flexiones de brazos', 3, '12-15', '20 s', 'Circuito'],
                    ['Estocadas alternadas', 3, '20', '20 s', 'Circuito'],
                    ['Remo TRX o invertido', 3, '15', '20 s', 'Circuito'],
                    ['Mountain climbers', 3, '40 s', '20 s', 'Circuito'],
                ],
                'Día 3 · Cardio largo' => [
                    ['Bici, remo o elíptico continuo', 1, '40 min', '', 'Zona 2'],
                    ['Movilidad general', 1, '10 min', '', ''],
                ],
                'Día 4 · Intervalos en remo' => [
                    ['Remo ergómetro: 500 m fuerte', 5, '500 m', '2 min', ''],
                    ['Burpees', 3, '10', '60 s', ''],
                    ['Farmer walk', 3, '40 m', '60 s', ''],
                    ['Plancha lateral', 3, '30 s por lado', '30 s', ''],
                ],
            ],
        ],

        // ── Salud general ────────────────────────────────────────────────
        'R12' => [
            'nombre' => 'R12 · Salud general · 2 días esenciales', 'objetivo' => 'Salud general', 'nivel' => 'principiante', 'dias' => 2,
            'resumen' => 'Lo mínimo recomendado: fuerza de todo el cuerpo y cardio moderado dos veces por semana.',
            'observaciones' => self::CALOR."\nIntensidad moderada: terminar cada serie sintiendo que quedan 3-4 repeticiones. Complementar con caminatas en la semana.",
            'semana' => [
                'Día 1 · Fuerza + cardio' => [
                    ['Prensa de piernas', 2, '12-15', '60 s', ''],
                    ['Press de pecho en máquina', 2, '12-15', '60 s', ''],
                    ['Remo sentado en polea', 2, '12-15', '60 s', ''],
                    ['Puente de glúteos', 2, '15', '45 s', ''],
                    ['Bicicleta fija', 1, '15 min', '', ''],
                ],
                'Día 2 · Fuerza + cardio' => [
                    ['Sentadilla a banco', 2, '12-15', '60 s', ''],
                    ['Jalón al pecho', 2, '12-15', '60 s', ''],
                    ['Press de hombros en máquina', 2, '12', '60 s', ''],
                    ['Bird dog', 2, '10 por lado', '30 s', ''],
                    ['Cinta caminando', 1, '15 min', '', ''],
                ],
            ],
        ],
        'R13' => [
            'nombre' => 'R13 · Salud general · Equilibrada 3 días', 'objetivo' => 'Salud general', 'nivel' => 'intermedio', 'dias' => 3,
            'resumen' => 'Fuerza, cardio y movilidad repartidos en la semana para sentirse bien y prevenir lesiones.',
            'observaciones' => self::CALOR."\nPriorizar constancia sobre intensidad. Ajustar cargas según cómo llega el socio cada día.",
            'semana' => [
                'Día 1 · Fuerza' => [
                    ['Sentadilla goblet', 3, '10-12', '90 s', ''],
                    ['Press de pecho con mancuernas', 3, '10-12', '90 s', ''],
                    ['Remo con mancuerna a una mano', 3, '10-12', '60 s', ''],
                    ['Peso muerto rumano con mancuernas', 3, '10-12', '60 s', ''],
                    ['Plancha abdominal', 3, '30-40 s', '30 s', ''],
                ],
                'Día 2 · Cardio + movilidad' => [
                    ['Elíptico o bici', 1, '25 min', '', 'Ritmo moderado'],
                    ['Movilidad de columna (gato-camello)', 2, '10', '', ''],
                    ['Estiramiento de cadera y femorales', 2, '30 s', '', ''],
                    ['Rotaciones de hombro con banda', 2, '15', '', ''],
                ],
                'Día 3 · Fuerza' => [
                    ['Prensa de piernas', 3, '12', '90 s', ''],
                    ['Jalón al pecho', 3, '12', '60 s', ''],
                    ['Press de hombros con mancuernas', 3, '12', '60 s', ''],
                    ['Estocadas alternadas', 2, '10 por pierna', '60 s', ''],
                    ['Farmer walk', 3, '30 m', '60 s', ''],
                ],
            ],
        ],

        // ── Rehabilitación / volver a entrenar ───────────────────────────
        'R14' => [
            'nombre' => 'R14 · Volver a entrenar · Adaptación', 'objetivo' => 'Rehabilitación / volver a entrenar', 'nivel' => 'principiante', 'dias' => 2,
            'resumen' => 'Para quien vuelve tras mucho tiempo sin entrenar: cargas livianas, máquinas guiadas y movilidad.',
            'observaciones' => self::CALOR."\nNingún ejercicio debe provocar dolor. Si hay una lesión o indicación médica, adaptar con el kinesiólogo. Mantener esta rutina 3-4 semanas antes de pasar a una de salud general.",
            'semana' => [
                'Día 1 · Adaptación A' => [
                    ['Bicicleta fija', 1, '10 min', '', 'Suave'],
                    ['Prensa de piernas', 2, '12-15', '90 s', 'Peso liviano'],
                    ['Remo sentado en polea', 2, '12-15', '90 s', ''],
                    ['Press de pecho en máquina', 2, '12', '90 s', ''],
                    ['Puente de glúteos', 2, '12', '60 s', ''],
                    ['Respiración diafragmática acostado', 2, '1 min', '', ''],
                ],
                'Día 2 · Adaptación B' => [
                    ['Cinta caminando', 1, '10 min', '', 'Suave'],
                    ['Sentadilla a banco', 2, '10-12', '90 s', 'Sin peso o con poco'],
                    ['Jalón al pecho', 2, '12', '90 s', ''],
                    ['Rotación externa de hombro con banda', 2, '15', '45 s', ''],
                    ['Bird dog', 2, '8 por lado', '45 s', ''],
                    ['Movilidad de cadera y tobillo', 1, '5 min', '', ''],
                ],
            ],
        ],
        'R15' => [
            'nombre' => 'R15 · Volver a entrenar · Estabilidad y core', 'objetivo' => 'Rehabilitación / volver a entrenar', 'nivel' => 'principiante', 'dias' => 3,
            'resumen' => 'Fortalece zona media, hombros y rodillas para retomar con seguridad. Útil tras molestias de espalda u hombro.',
            'observaciones' => self::CALOR."\nTrabajar lento y sin dolor. Revisar con el socio cualquier molestia previa (comentarios del pedido). Ante dolor agudo, suspender y derivar.",
            'semana' => [
                'Día 1 · Core y espalda' => [
                    ['Bicicleta fija', 1, '8 min', '', ''],
                    ['Dead bug', 3, '8 por lado', '45 s', ''],
                    ['Bird dog', 3, '8 por lado', '45 s', ''],
                    ['Plancha sobre rodillas', 3, '20-30 s', '45 s', ''],
                    ['Remo sentado en polea', 3, '12', '60 s', 'Peso liviano'],
                    ['Puente de glúteos', 3, '12', '45 s', ''],
                ],
                'Día 2 · Hombros y escápulas' => [
                    ['Elíptico', 1, '10 min', '', ''],
                    ['Rotación externa de hombro con banda', 3, '15', '45 s', ''],
                    ['Face pull liviano', 3, '15', '45 s', ''],
                    ['Deslizamientos en pared (wall slides)', 3, '10', '45 s', ''],
                    ['Press de pecho en máquina liviano', 2, '12', '60 s', 'Rango sin dolor'],
                    ['Jalón al pecho liviano', 2, '12', '60 s', ''],
                ],
                'Día 3 · Rodillas y cadera' => [
                    ['Bicicleta fija', 1, '10 min', '', ''],
                    ['Sentadilla a banco', 3, '10', '60 s', ''],
                    ['Step up bajo', 3, '10 por pierna', '60 s', ''],
                    ['Extensión de cuádriceps liviana', 2, '15', '45 s', ''],
                    ['Abducción de cadera con banda', 3, '15', '45 s', ''],
                    ['Equilibrio a una pierna', 3, '30 s por pierna', '30 s', ''],
                ],
            ],
        ],
    ];
}
