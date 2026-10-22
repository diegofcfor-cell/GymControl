<?php

namespace App\Http\Controllers;

use App\Http\Requests\PreguntaFrecuenteRequest;
use App\Models\PreguntaFrecuente;

class PreguntaFrecuenteController extends Controller
{
    public function index()
    {
        return view('preguntas.index', ['preguntas' => PreguntaFrecuente::orderBy('orden')->orderBy('id')->get()]);
    }

    public function create()
    {
        return view('preguntas.form', ['pregunta' => new PreguntaFrecuente(['activa' => true, 'orden' => 0])]);
    }

    public function store(PreguntaFrecuenteRequest $request)
    {
        PreguntaFrecuente::create($this->datos($request));

        return redirect()->route('preguntas.index')->with('ok', 'Pregunta creada.');
    }

    public function edit(PreguntaFrecuente $pregunta)
    {
        return view('preguntas.form', compact('pregunta'));
    }

    public function update(PreguntaFrecuenteRequest $request, PreguntaFrecuente $pregunta)
    {
        $pregunta->update($this->datos($request));

        return redirect()->route('preguntas.index')->with('ok', 'Pregunta actualizada.');
    }

    public function destroy(PreguntaFrecuente $pregunta)
    {
        $pregunta->delete();

        return redirect()->route('preguntas.index')->with('ok', 'Pregunta eliminada.');
    }

    protected function datos(PreguntaFrecuenteRequest $request): array
    {
        $datos = $request->validated();
        $datos['orden'] ??= 0;
        $datos['activa'] = $request->boolean('activa');

        return $datos;
    }
}
