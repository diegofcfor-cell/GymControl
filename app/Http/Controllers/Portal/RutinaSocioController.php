<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\SolicitudRutina;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    public function solicitar(Request $request)
    {
        $socio = $request->attributes->get('socio');

        if ($socio->solicitudesRutina()->pendientes()->exists()) {
            return redirect()->route('portal.rutina')->with('ok', 'Ya tenés un pedido en curso. Un profesor te va a armar la rutina.');
        }

        $datos = $request->validate([
            'objetivo' => ['required', Rule::in(SolicitudRutina::OBJETIVOS)],
            'dias_por_semana' => ['required', 'integer', 'min:1', 'max:7'],
            'nivel' => ['required', Rule::in(array_keys(SolicitudRutina::NIVELES))],
            'comentarios' => ['nullable', 'string', 'max:1000'],
        ]);

        $socio->solicitudesRutina()->create($datos + ['estado' => 'pendiente']);

        return redirect()->route('portal.rutina')->with('ok', '¡Listo! Un profesor va a armar tu rutina y la vas a ver acá.');
    }
}
