<?php

namespace App\Http\Controllers;

use App\Http\Requests\AptoFisicoRequest;
use App\Models\AptoFisico;
use App\Models\Socio;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class AptoFisicoController extends Controller
{
    public function store(AptoFisicoRequest $request, Socio $socio)
    {
        $datos = $request->validated();

        $ruta = $request->file('archivo')?->store("socio-{$socio->id}", 'certificados');

        $socio->aptos()->create([
            'user_id' => $request->user()->id,
            'fecha_emision' => $datos['fecha_emision'],
            'fecha_vencimiento' => $datos['fecha_vencimiento'],
            'estado' => Carbon::parse($datos['fecha_vencimiento'])->gte(today()) ? 'vigente' : 'vencido',
            'archivo' => $ruta,
        ]);

        return back()->with('ok', 'Apto físico registrado.');
    }

    /** Descarga protegida por sesión (los certificados no son públicos). */
    public function archivo(AptoFisico $apto)
    {
        Gate::authorize('view', $apto);
        abort_unless($apto->archivo && Storage::disk('certificados')->exists($apto->archivo), 404);

        return Storage::disk('certificados')->response($apto->archivo);
    }
}
