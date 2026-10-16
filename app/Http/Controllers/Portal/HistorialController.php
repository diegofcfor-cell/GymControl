<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AptoFisico;
use App\Models\Pago;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Historiales propios del socio en el portal. Todo se consulta a través de la relación
 * del socio con sesión, y un pago o apto de otro socio responde 404.
 */
class HistorialController extends Controller
{
    public function asistencias(Request $request)
    {
        $socio = $request->attributes->get('socio');
        $filtros = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);

        $consulta = $socio->asistencias()
            ->when($filtros['desde'] ?? null, fn ($q, $d) => $q->whereDate('fecha', '>=', $d))
            ->when($filtros['hasta'] ?? null, fn ($q, $h) => $q->whereDate('fecha', '<=', $h));

        $minutos = (clone $consulta)->get()->sum(fn ($a) => $a->minutosPermanencia() ?? 0);

        return view('portal.asistencias', [
            'asistencias' => $consulta->orderByDesc('fecha')->orderByDesc('hora_ingreso')->paginate(20)->withQueryString(),
            'horasTotal' => round($minutos / 60, 1),
        ]);
    }

    public function pagos(Request $request)
    {
        $socio = $request->attributes->get('socio');

        return view('portal.pagos', [
            'socio' => $socio,
            'pagos' => $socio->pagos()->with('membresia')->orderByDesc('fecha_pago')->orderByDesc('id')->paginate(20),
        ]);
    }

    public function comprobante(Request $request, Pago $pago)
    {
        $socio = $request->attributes->get('socio');
        abort_unless($pago->socio_id === $socio->id, 404);

        return Pdf::loadView('pdf.comprobante', ['pago' => $pago->load('membresia', 'operador'), 'socio' => $socio])
            ->setPaper('a5')
            ->stream('comprobante-'.str_pad((string) $pago->id, 6, '0', STR_PAD_LEFT).'.pdf');
    }

    public function aptos(Request $request)
    {
        $socio = $request->attributes->get('socio');

        return view('portal.aptos', ['aptos' => $socio->aptos()->orderByDesc('fecha_vencimiento')->get()]);
    }

    public function aptoArchivo(Request $request, AptoFisico $apto)
    {
        $socio = $request->attributes->get('socio');
        abort_unless($apto->socio_id === $socio->id, 404);
        abort_unless($apto->archivo && Storage::disk('certificados')->exists($apto->archivo), 404);

        return Storage::disk('certificados')->response($apto->archivo);
    }
}
