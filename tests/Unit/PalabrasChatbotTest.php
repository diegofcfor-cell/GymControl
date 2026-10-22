<?php

namespace Tests\Unit;

use App\Repositories\AsistenciaRepository;
use App\Services\AforoService;
use App\Services\ChatbotService;
use PHPUnit\Framework\TestCase;

/** Normalización del mensaje del socio antes de buscar en las preguntas frecuentes. */
class PalabrasChatbotTest extends TestCase
{
    public function test_quita_tildes_signos_mayusculas_y_palabras_vacias(): void
    {
        $palabras = (new ChatbotService(new AforoService(new AsistenciaRepository)))->palabras('¿Hola! ¿Cuál es el HORARIO del gimnasio los sábados?');

        $this->assertSame(['horario', 'sabados'], $palabras);
    }

    public function test_no_repite_palabras_y_un_mensaje_vacio_no_deja_palabras(): void
    {
        $chatbot = new ChatbotService(new AforoService(new AsistenciaRepository));

        $this->assertSame(['precio'], $chatbot->palabras('precio precio PRECIO'));
        $this->assertSame([], $chatbot->palabras('¿?  de la'));
    }
}
