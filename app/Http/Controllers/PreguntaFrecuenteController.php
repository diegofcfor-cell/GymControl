<?php

namespace App\Http\Controllers;

use App\Models\PreguntaFrecuente;
use Illuminate\Http\Request;

class PreguntaFrecuenteController extends Controller
{
    protected array $reglas = [
        'pregunta' => ['required', 'string', 'max:200'],
        'respuesta' => ['required', 'string', 'max:2000'],
        'palabras_clave' => ['nullable', 'string', 'max:255'],
        'orden' => ['nullable', 'integer', 'min:0', 'max:999'],
    ];

    public function index()
    {
        return view('preguntas.index', ['preguntas' => PreguntaFrecuente::orderBy('orden')->orderBy('id')->get()]);
    }

    public function create()
    {
        return view('preguntas.form', ['pregunta' => new PreguntaFrecuente(['activa' => true, 'orden' => 0])]);
    }

    public function store(Request $request)
    {
        PreguntaFrecuente::create($this->datos($request));

        return redirect()->route('preguntas.index')->with('ok', 'Pregunta creada.');
    }

    public function edit(PreguntaFrecuente $pregunta)
    {
        return view('preguntas.form', compact('pregunta'));
    }

    public function update(Request $request, PreguntaFrecuente $pregunta)
    {
        $pregunta->update($this->datos($request));

        return redirect()->route('preguntas.index')->with('ok', 'Pregunta actualizada.');
    }

    public function destroy(PreguntaFrecuente $pregunta)
    {
        $pregunta->delete();

        return redirect()->route('preguntas.index')->with('ok', 'Pregunta eliminada.');
    }

    protected function datos(Request $request): array
    {
        $datos = $request->validate($this->reglas);
        $datos['orden'] ??= 0;
        $datos['activa'] = $request->boolean('activa');

        return $datos;
    }
}
