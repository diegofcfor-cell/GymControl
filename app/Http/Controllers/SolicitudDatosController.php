<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResolverSolicitudDatosRequest;
use App\Models\SolicitudDatos;
use App\Services\SolicitudDatosService;

/** Bandeja del administrador con los pedidos de rectificación y supresión de los socios. */
class SolicitudDatosController extends Controller
{
    public function index()
    {
        return view('solicitudes-datos.index', [
            'pendientes' => SolicitudDatos::with('socio')->pendientes()->oldest()->get(),
            'resueltas' => SolicitudDatos::with(['socio', 'operador'])->where('estado', 'resuelta')->latest('resuelta_at')->limit(30)->get(),
            'tipos' => SolicitudDatos::TIPOS,
        ]);
    }

    public function resolver(ResolverSolicitudDatosRequest $request, SolicitudDatos $solicitud, SolicitudDatosService $servicio)
    {
        $servicio->resolver($solicitud, $request->user(), $request->validated('respuesta'));

        return redirect()->route('solicitudes-datos.index')->with('ok', 'Respuesta enviada al portal del socio.');
    }
}
