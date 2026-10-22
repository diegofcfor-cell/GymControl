<?php

namespace App\Http\Controllers;

use App\Http\Requests\MembresiaRequest;
use App\Models\Membresia;

class MembresiaController extends Controller
{
    public function index()
    {
        return view('membresias.index', ['membresias' => Membresia::withCount('socios')->orderBy('precio')->get()]);
    }

    public function create()
    {
        return view('membresias.form', ['membresia' => new Membresia(['estado' => 'activa', 'duracion_dias' => 30])]);
    }

    public function store(MembresiaRequest $request)
    {
        Membresia::create($request->validated());

        return redirect()->route('membresias.index')->with('ok', 'Membresía creada.');
    }

    public function edit(Membresia $membresia)
    {
        return view('membresias.form', compact('membresia'));
    }

    public function update(MembresiaRequest $request, Membresia $membresia)
    {
        $membresia->update($request->validated());

        return redirect()->route('membresias.index')->with('ok', 'Membresía actualizada.');
    }
}
