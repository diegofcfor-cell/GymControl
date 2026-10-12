<?php

namespace App\Livewire;

use App\Services\AccesoService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/** Pantalla de ingreso / egreso para la tablet o PC ubicada en el acceso del gimnasio. */
#[Layout('components.layouts.kiosco')]
#[Title('Acceso')]
class PantallaAcceso extends Component
{
    public string $codigo = '';

    /** @var array<string,mixed>|null */
    public ?array $resultado = null;

    public function registrar(AccesoService $servicio): void
    {
        $r = $servicio->procesar($this->codigo, request()->ip());
        $socio = $r['socio'];

        $this->resultado = [
            'ok' => $r['ok'],
            'tipo' => $r['tipo'],
            'mensaje' => $r['mensaje'],
            'motivos' => $r['motivos'],
            'hora' => $r['hora'],
            'permanencia' => $r['permanencia'],
            'aviso' => $r['aviso'],
            'socio' => $socio?->nombre_completo,
            'plan' => $socio?->membresia?->nombre,
            'vence' => $socio?->fecha_vencimiento?->format('d/m/Y'),
        ];

        $this->codigo = '';
        $this->dispatch('enfocar');
    }

    public function limpiar(): void
    {
        $this->resultado = null;
    }

    public function render()
    {
        return view('livewire.pantalla-acceso');
    }
}
