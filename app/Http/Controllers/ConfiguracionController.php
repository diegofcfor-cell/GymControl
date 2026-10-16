<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Services\AforoService;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function edit(AforoService $aforo)
    {
        return view('configuracion.edit', ['capacidad' => $aforo->capacidad()]);
    }

    public function update(Request $request)
    {
        $datos = $request->validate([
            'capacidad_maxima' => ['required', 'integer', 'min:1', 'max:5000'],
        ]);

        Configuracion::guardar('capacidad_maxima', (string) $datos['capacidad_maxima']);

        return redirect()->route('configuracion.edit')->with('ok', 'Configuración guardada.');
    }
}
