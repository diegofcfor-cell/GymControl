<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfiguracionRequest;
use App\Models\Configuracion;
use App\Services\AforoService;

class ConfiguracionController extends Controller
{
    public function edit(AforoService $aforo)
    {
        return view('configuracion.edit', [
            'capacidad' => $aforo->capacidad(),
            'accesoQr' => Configuracion::accesoQrHabilitado(),
            'web' => Configuracion::varios(Configuracion::DATOS_WEB),
        ]);
    }

    public function update(ConfiguracionRequest $request)
    {
        foreach ($request->validated() as $clave => $valor) {
            Configuracion::guardar($clave, $valor === null ? null : trim((string) $valor));
        }

        return redirect()->route('configuracion.edit')->with('ok', 'Configuración guardada.');
    }
}
