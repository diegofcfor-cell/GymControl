<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    public function store(Request $request)
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'rol' => ['required', Rule::in([User::ROL_ADMIN, User::ROL_RECEPCION])],
        ]);
        User::create($datos + ['activo' => true]);

        return redirect()->route('operadores.index')->with('ok', 'Operador creado.');
    }

    public function edit(User $operador)
    {
        return view('operadores.form', compact('operador'));
    }

    public function update(Request $request, User $operador)
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($operador->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'rol' => ['required', Rule::in([User::ROL_ADMIN, User::ROL_RECEPCION])],
            'activo' => ['nullable', 'boolean'],
        ]);

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
