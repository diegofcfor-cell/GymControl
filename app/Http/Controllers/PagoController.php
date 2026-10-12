<?php

namespace App\Http\Controllers;

use App\Models\Membresia;
use App\Models\Pago;
use App\Models\Socio;
use App\Services\PagoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    protected function consulta(Request $request)
    {
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');
        $texto = $request->input('q');

        return Pago::with(['socio', 'membresia', 'operador'])
            ->when($desde, fn ($q) => $q->whereDate('fecha_pago', '>=', $desde))
            ->when($hasta, fn ($q) => $q->whereDate('fecha_pago', '<=', $hasta))
            ->when($texto, fn ($q) => $q->whereHas('socio', fn ($s) => $s->buscar($texto)))
            ->latest('fecha_pago')->latest('id');
    }

    public function index(Request $request)
    {
        $pagos = $this->consulta($request)->paginate(20)->withQueryString();
        $total = (float) $this->consulta($request)->sum('importe');

        return view('pagos.index', compact('pagos', 'total'));
    }

    public function pdf(Request $request)
    {
        $pagos = $this->consulta($request)->get();

        return Pdf::loadView('pdf.pagos', [
            'pagos' => $pagos,
            'total' => $pagos->sum('importe'),
            'desde' => $request->input('desde'),
            'hasta' => $request->input('hasta'),
        ])->setPaper('a4', 'landscape')->stream('historial-pagos.pdf');
    }

    public function create(Socio $socio)
    {
        return view('pagos.create', ['socio' => $socio->load('membresia'), 'membresias' => Membresia::activas()->orderBy('precio')->get()]);
    }

    public function store(Request $request, Socio $socio, PagoService $servicio)
    {
        $datos = $request->validate([
            'membresia_id' => ['required', 'exists:membresias,id'],
            'metodo_pago' => ['required', 'in:efectivo,transferencia,debito,credito'],
        ]);

        $pago = $servicio->registrar($socio, Membresia::findOrFail($datos['membresia_id']), $datos['metodo_pago'], $request->user());

        return redirect()->route('socios.show', $socio)
            ->with('ok', 'Pago registrado. Cuota vigente hasta el '.$pago->periodo_hasta->format('d/m/Y').'.');
    }
}
