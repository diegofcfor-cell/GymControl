<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\SolicitudRutinaRequest;
use App\Models\SolicitudRutina;
use Illuminate\Http\Request;

class RutinaSocioController extends Controller
{
    public function show(Request $request)
    {
        $socio = $request->attributes->get('socio');
        $rutina = $socio->rutinaActiva()?->load('ejercicios', 'profesor');

        return view('portal.rutina', [
            'rutina' => $rutina,
            'pendiente' => $socio->solicitudesRutina()->pendientes()->latest('id')->first(),
            'objetivos' => SolicitudRutina::OBJETIVOS,
            'niveles' => SolicitudRutina::NIVELES,
        ]);
    }

    public function solicitar(SolicitudRutinaRequest $request)
    {
        $socio = $request->attributes->get('socio');

        if ($socio->solicitudesRutina()->pendientes()->exists()) {
            return redirect()->route('portal.rutina')->with('ok', 'Ya tenés un pedido en curso. Un profesor te va a armar la rutina.');
        }

        $socio->solicitudesRutina()->create($request->validated() + ['estado' => 'pendiente']);

        return redirect()->route('portal.rutina')->with('ok', '¡Listo! Un profesor va a armar tu rutina y la vas a ver acá.');
    }
}
