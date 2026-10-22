<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\SolicitudDatosRequest;
use App\Models\SolicitudDatos;
use App\Services\SolicitudDatosService;
use Illuminate\Http\Request;

/** Derecho de acceso, rectificación y supresión del socio (Ley 25.326, arts. 14 y 16). */
class MisDatosController extends Controller
{
    public function show(Request $request)
    {
        $socio = $request->attributes->get('socio')->load('membresia');

        return view('portal.mis-datos', [
            'socio' => $socio,
            'solicitudes' => $socio->solicitudesDatos()->latest('id')->get(),
            'tipos' => SolicitudDatos::TIPOS,
        ]);
    }

    public function solicitar(SolicitudDatosRequest $request, SolicitudDatosService $servicio)
    {
        $solicitud = $servicio->registrar($request->attributes->get('socio'), $request->validated('tipo'), $request->validated('detalle'));

        return redirect()->route('portal.mis-datos')->with('ok', $solicitud
            ? 'Recibimos tu pedido. Te respondemos acá antes del '.$solicitud->plazo()->format('d/m/Y').'.'
            : 'Ya tenés un pedido de ese tipo en curso. Te respondemos acá.');
    }
}
