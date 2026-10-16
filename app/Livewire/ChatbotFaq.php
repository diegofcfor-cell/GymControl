<?php

namespace App\Livewire;

use App\Services\ChatbotService;
use Livewire\Component;

/** Chat de preguntas frecuentes del portal del socio. */
class ChatbotFaq extends Component
{
    public const MAX_MENSAJES = 30;

    public string $mensaje = '';

    /** @var array<int,array{de:string,texto:string,sugerencias?:array<int,string>}> */
    public array $conversacion = [];

    public function mount(): void
    {
        $this->conversacion[] = [
            'de' => 'bot',
            'texto' => '¡Hola! Preguntame sobre horarios, planes, precios, clases o cuánta gente hay ahora.',
        ];
    }

    public function enviar(ChatbotService $chatbot): void
    {
        $texto = trim(mb_substr($this->mensaje, 0, 300));
        $this->mensaje = '';
        if ($texto === '') {
            return;
        }

        $r = $chatbot->responder($texto);
        $this->conversacion[] = ['de' => 'socio', 'texto' => $texto];
        $this->conversacion[] = ['de' => 'bot', 'texto' => $r['respuesta'], 'sugerencias' => $r['sugerencias']];

        // Mantener acotado el estado que viaja en cada request de Livewire.
        $this->conversacion = array_slice($this->conversacion, -self::MAX_MENSAJES);
    }

    public function preguntar(string $pregunta): void
    {
        $this->mensaje = $pregunta;
        $this->enviar(app(ChatbotService::class));
    }

    public function render()
    {
        return view('livewire.chatbot-faq');
    }
}
