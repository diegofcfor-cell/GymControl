<?php

namespace App\Http\Controllers;

use App\Http\Requests\SocioRequest;
use App\Models\Membresia;
use App\Models\Socio;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SocioController extends Controller
{
    public function create()
    {
        return view('socios.form', ['socio' => new Socio, 'membresias' => Membresia::activas()->orderBy('precio')->get()]);
    }

    public function store(SocioRequest $request)
    {
        $socio = Socio::create($request->safe()->except('consentimiento_datos') + [
            'user_id' => $request->user()->id, 'estado' => 'activo', 'consentimiento_datos_at' => now(),
        ]);

        return redirect()->route('socios.show', $socio)
            ->with('ok', 'Socio registrado. Cargue ahora el apto físico y registre el primer pago para habilitar su ingreso.');
    }

    public function show(Socio $socio)
    {
        $socio->load(['membresia', 'operador']);

        return view('socios.show', [
            'socio' => $socio,
            'aptos' => $socio->aptos()->orderByDesc('fecha_vencimiento')->get(),
            'pagos' => $socio->pagos()->with('membresia')->latest('fecha_pago')->latest('id')->limit(10)->get(),
            'asistencias' => $socio->asistencias()->latest('fecha')->latest('id')->limit(10)->get(),
        ]);
    }

    public function edit(Socio $socio)
    {
        return view('socios.form', ['socio' => $socio, 'membresias' => Membresia::orderBy('precio')->get()]);
    }

    public function update(SocioRequest $request, Socio $socio)
    {
        $socio->fill($request->safe()->except('consentimiento_datos'));
        if ($request->boolean('consentimiento_datos') && ! $socio->dioConsentimiento()) {
            $socio->consentimiento_datos_at = now();
        }
        $socio->save();

        return redirect()->route('socios.show', $socio)->with('ok', 'Datos del socio actualizados.');
    }

    /** Baja lógica (solo administrador): se conserva todo el historial. */
    public function destroy(Socio $socio)
    {
        Gate::authorize('delete', $socio);
        $socio->update(['estado' => 'inactivo']);
        $socio->delete();

        return redirect()->route('socios.index')->with('ok', 'Socio dado de baja. Su historial se conserva.');
    }

    public function restore(Socio $socio)
    {
        Gate::authorize('restore', $socio);
        $socio->restore();
        $socio->update(['estado' => 'activo']);

        return redirect()->route('socios.show', $socio)->with('ok', 'Socio reactivado.');
    }

    /** Credencial imprimible con PIN y código QR. */
    public function credencial(Socio $socio)
    {
        return view('socios.credencial', ['socio' => $socio->load('membresia'), 'qr' => $this->qrBase64($socio)]);
    }

    public function fichaPdf(Socio $socio)
    {
        $socio->load(['membresia', 'operador']);

        return Pdf::loadView('pdf.ficha', [
            'socio' => $socio,
            'aptos' => $socio->aptos()->orderByDesc('fecha_vencimiento')->get(),
            'pagos' => $socio->pagos()->with('membresia')->latest('fecha_pago')->get(),
            'asistencias' => $socio->asistencias()->latest('fecha')->latest('id')->limit(30)->get(),
            'qr' => $this->qrBase64($socio),
        ])->setPaper('a4')->stream("ficha-{$socio->dni}.pdf");
    }

    protected function qrBase64(Socio $socio): string
    {
        return base64_encode(QrCode::format('svg')->size(220)->margin(1)->generate($socio->qr_codigo));
    }
}
