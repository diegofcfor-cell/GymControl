<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Services\AforoService;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function edit(AforoService $aforo)
    {
        return view('configuracion.edit', [
            'capacidad' => $aforo->capacidad(),
            'web' => Configuracion::varios(Configuracion::DATOS_WEB),
        ]);
    }

    public function update(Request $request)
    {
        $datos = $request->validate([
            'capacidad_maxima' => ['required', 'integer', 'min:1', 'max:5000'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'servicios' => ['nullable', 'string', 'max:2000'],
            'horarios' => ['nullable', 'string', 'max:1000'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:100'],
            'instagram' => ['nullable', 'string', 'max:60', 'regex:/^@?[A-Za-z0-9._]+$/'],
        ]);

        foreach ($datos as $clave => $valor) {
            Configuracion::guardar($clave, $valor === null ? null : trim((string) $valor));
        }

        return redirect()->route('configuracion.edit')->with('ok', 'Configuración guardada.');
    }
}
