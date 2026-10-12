<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    protected function consulta(Request $request)
    {
        return Asistencia::with('socio')
            ->when($request->input('desde'), fn ($q, $v) => $q->whereDate('fecha', '>=', $v))
            ->when($request->input('hasta'), fn ($q, $v) => $q->whereDate('fecha', '<=', $v))
            ->when($request->input('q'), fn ($q, $v) => $q->whereHas('socio', fn ($s) => $s->buscar($v)))
            ->when($request->input('socio'), fn ($q, $v) => $q->where('socio_id', $v))
            ->latest('fecha')->latest('hora_ingreso');
    }

    public function index(Request $request)
    {
        return view('asistencias.index', ['asistencias' => $this->consulta($request)->paginate(25)->withQueryString()]);
    }

    public function pdf(Request $request)
    {
        $asistencias = $this->consulta($request)->get();

        return Pdf::loadView('pdf.asistencias', [
            'asistencias' => $asistencias,
            'desde' => $request->input('desde'),
            'hasta' => $request->input('hasta'),
            'minutos' => $asistencias->sum(fn ($a) => $a->minutosPermanencia() ?? 0),
        ])->setPaper('a4')->stream('historial-asistencias.pdf');
    }
}
