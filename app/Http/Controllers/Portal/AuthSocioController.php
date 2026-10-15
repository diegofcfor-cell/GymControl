<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Socio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AuthSocioController extends Controller
{
    /** Intentos fallidos permitidos por DNI antes de bloquear. */
    public const MAX_INTENTOS = 5;

    /** Segundos de bloqueo tras superar los intentos. */
    public const BLOQUEO_SEGUNDOS = 900;

    public function showLogin(Request $request)
    {
        if ($request->session()->has('socio_id')) {
            return redirect()->route('portal.inicio');
        }

        return view('portal.login');
    }

    public function login(Request $request)
    {
        $datos = $request->validate([
            'dni' => ['required', 'string', 'max:15'],
            'pin' => ['required', 'digits:6'],
        ]);

        // El PIN tiene solo 6 dígitos: se exige también el DNI y se limitan los intentos por DNI.
        $clave = 'portal-socio:'.$datos['dni'];
        if (RateLimiter::tooManyAttempts($clave, self::MAX_INTENTOS)) {
            $minutos = (int) ceil(RateLimiter::availableIn($clave) / 60);

            return back()->withErrors(['dni' => "Demasiados intentos. Probá de nuevo en {$minutos} min."])->onlyInput('dni');
        }

        $socio = Socio::where('dni', $datos['dni'])->first();

        if (! $socio || ! hash_equals($socio->pin, $datos['pin']) || ! $socio->estaActivo()) {
            RateLimiter::hit($clave, self::BLOQUEO_SEGUNDOS);

            return back()->withErrors(['dni' => 'DNI o PIN incorrectos.'])->onlyInput('dni');
        }

        RateLimiter::clear($clave);
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
