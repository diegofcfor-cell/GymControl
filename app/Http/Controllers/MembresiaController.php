<?php

namespace App\Http\Controllers;

use App\Models\Membresia;
use Illuminate\Http\Request;

class MembresiaController extends Controller
{
    protected array $reglas = [
        'nombre' => ['required', 'string', 'max:50'],
        'precio' => ['required', 'numeric', 'min:0', 'max:99999999'],
        'duracion_dias' => ['required', 'integer', 'min:1', 'max:366'],
        'horas_diarias_max' => ['nullable', 'integer', 'min:1', 'max:24'],
        'estado' => ['required', 'in:activa,inactiva'],
    ];

    public function index()
    {
        return view('membresias.index', ['membresias' => Membresia::withCount('socios')->orderBy('precio')->get()]);
    }

    public function create()
    {
        return view('membresias.form', ['membresia' => new Membresia(['estado' => 'activa', 'duracion_dias' => 30])]);
    }

    public function store(Request $request)
    {
        Membresia::create($request->validate($this->reglas));

        return redirect()->route('membresias.index')->with('ok', 'Membresía creada.');
    }

    public function edit(Membresia $membresia)
    {
        return view('membresias.form', compact('membresia'));
    }

    public function update(Request $request, Membresia $membresia)
    {
        $membresia->update($request->validate($this->reglas));

        return redirect()->route('membresias.index')->with('ok', 'Membresía actualizada.');
    }
}
