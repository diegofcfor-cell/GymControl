<?php

namespace App\Http\Controllers;

use App\Http\Requests\CambiarClaveRequest;
use App\Services\OperadorService;

/** "Mi contraseña": cada operador (cualquier rol) cambia su propia contraseña. */
class CuentaController extends Controller
{
    public function editarClave()
    {
        return view('cuenta.clave');
    }

    public function actualizarClave(CambiarClaveRequest $request, OperadorService $operadores)
    {
        $operadores->cambiarClave($request->user(), $request->validated('password'), $request->session()->getId());
        $request->session()->regenerate();

        return redirect()->route('inicio')->with('ok', 'Contraseña actualizada. Las demás sesiones abiertas con tu usuario se cerraron.');
    }
}
