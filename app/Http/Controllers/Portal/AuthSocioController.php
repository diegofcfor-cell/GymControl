<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginSocioRequest;
use App\Services\AutenticacionSocioService;
use Illuminate\Http\Request;

class AuthSocioController extends Controller
{
    public function showLogin(Request $request)
    {
        if ($request->session()->has('socio_id')) {
            return redirect()->route('portal.inicio');
        }

        return view('portal.login');
    }

    public function login(LoginSocioRequest $request, AutenticacionSocioService $autenticacion)
    {
        $datos = $request->validated();

        ['socio' => $socio, 'error' => $error] = $autenticacion->intentar($datos['dni'], $datos['pin']);

        if (! $socio) {
            return back()->withErrors(['dni' => $error])->onlyInput('dni');
        }

        $request->session()->regenerate();
        $request->session()->put('socio_id', $socio->id);

        return redirect()->route('portal.inicio');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('socio_id');
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
