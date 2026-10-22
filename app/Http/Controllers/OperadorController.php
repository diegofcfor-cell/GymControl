<?php

namespace App\Http\Controllers;

use App\Http\Requests\OperadorRequest;
use App\Models\User;
use App\Services\OperadorService;

class OperadorController extends Controller
{
    public function index()
    {
        return view('operadores.index', ['operadores' => User::orderBy('rol')->orderBy('name')->get()]);
    }

    public function create()
    {
        return view('operadores.form', ['operador' => new User(['rol' => User::ROL_RECEPCION, 'activo' => true])]);
    }

    public function store(OperadorRequest $request, OperadorService $operadores)
    {
        $operadores->crear($request->safe()->except('activo'));

        return redirect()->route('operadores.index')->with('ok', 'Operador creado.');
    }

    public function edit(User $operador)
    {
        return view('operadores.form', compact('operador'));
    }

    public function update(OperadorRequest $request, User $operador, OperadorService $operadores)
    {
        $operadores->actualizar($operador, ['activo' => $request->boolean('activo')] + $request->validated(), $request->user());

        return redirect()->route('operadores.index')->with('ok', 'Operador actualizado.');
    }
}
