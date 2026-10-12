<?php

namespace App\Livewire;

use App\Models\Socio;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Socios')]
class SociosIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $busqueda = '';

    #[Url]
    public string $filtro = 'todos'; // todos | al_dia | vencidos | bajas

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatingFiltro(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $consulta = Socio::with('membresia')->buscar($this->busqueda);

        $consulta = match ($this->filtro) {
            'al_dia' => $consulta->where('estado', 'activo')->conCuotaAlDia(),
            'vencidos' => $consulta->where('estado', 'activo')->conCuotaVencida(),
            'bajas' => $consulta->onlyTrashed(),
            default => $consulta,
        };

        return view('livewire.socios-index', [
            'socios' => $consulta->orderBy('apellido')->orderBy('nombre')->paginate(15),
        ]);
    }
}
