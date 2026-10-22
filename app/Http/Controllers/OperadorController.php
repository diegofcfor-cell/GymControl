<?php

namespace App\Http\Controllers;

use App\Http\Requests\OperadorRequest;
use App\Models\User;

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

    public function store(OperadorRequest $request)
    {
        User::create($request->safe()->except('activo') + ['activo' => true]);

        return redirect()->route('operadores.index')->with('ok', 'Operador creado.');
    }

    public function edit(User $operador)
    {
        return view('operadores.form', compact('operador'));
    }

    public function update(OperadorRequest $request, User $operador)
    {
        $datos = $request->validated();

        $datos['activo'] = $request->boolean('activo');

        // El administrador no puede quitarse a sí mismo el rol ni desactivarse.
        if ($operador->id === $request->user()->id) {
            $datos['rol'] = User::ROL_ADMIN;
            $datos['activo'] = true;
        }
        if (empty($datos['password'])) {
            unset($datos['password']);
        }

        $operador->update($datos);

        return redirect()->route('operadores.index')->with('ok', 'Operador actualizado.');
    }
}
