<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Membresia;

/** Página web pública del gimnasio. */
class WebController extends Controller
{
    public function inicio()
    {
        $web = Configuracion::varios(Configuracion::DATOS_WEB);

        return view('web.inicio', [
            'web' => $web,
            'servicios' => $this->lineas($web['servicios']),
            'horarios' => $this->lineas($web['horarios']),
            'planes' => Membresia::activas()->orderBy('precio')->get(),
        ]);
    }

    /** @return array<int,string> */
    protected function lineas(?string $texto): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $texto))));
    }
}
