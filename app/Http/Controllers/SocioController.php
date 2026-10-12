<?php

namespace App\Http\Controllers;

use App\Models\Membresia;
use App\Models\Socio;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SocioController extends Controller
{
    protected function reglas(?Socio $socio = null): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'dni' => ['required', 'digits_between:7,9', Rule::unique('socios', 'dni')->ignore($socio?->id)],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'membresia_id' => ['required', Rule::exists('membresias', 'id')->where('estado', 'activa')],
        ];
    }

    public function create()
    {
        return view('socios.form', ['socio' => new Socio, 'membresias' => Membresia::activas()->orderBy('precio')->get()]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate($this->reglas());
        $socio = Socio::create($datos + ['user_id' => $request->user()->id, 'estado' => 'activo']);

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

    public function update(Request $request, Socio $socio)
    {
        $reglas = $this->reglas($socio);
        // permitir conservar una membresía que luego fue desactivada
        $reglas['membresia_id'] = ['required', 'exists:membresias,id'];
        $socio->update($request->validate($reglas));

        return redirect()->route('socios.show', $socio)->with('ok', 'Datos del socio actualizados.');
    }

    /** Baja lógica (solo administrador): se conserva todo el historial. */
    public function destroy(Socio $socio)
    {
        $socio->update(['estado' => 'inactivo']);
        $socio->delete();

        return redirect()->route('socios.index')->with('ok', 'Socio dado de baja. Su historial se conserva.');
    }

    public function restore(Socio $socio)
    {
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
