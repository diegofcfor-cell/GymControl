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
            'fotos' => $this->fotosPortada(),
        ]);
    }

    /** Aviso de privacidad (Ley 25.326): qué datos se tratan, para qué y cómo ejercer los derechos. */
    public function privacidad()
    {
        return view('web.privacidad', ['web' => Configuracion::varios(Configuracion::DATOS_WEB)]);
    }

    /**
     * Fotos del fondo de la portada: las imágenes que haya en public/img/portada, en orden alfabético.
     *
     * @return array<int,string>
     */
    protected function fotosPortada(): array
    {
        $archivos = glob(public_path('img/portada/*')) ?: [];
        $fotos = array_filter($archivos, fn ($f) => in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true));
        sort($fotos);

        return array_map(fn ($f) => asset('img/portada/'.rawurlencode(basename($f))), array_values($fotos));
    }

    /** @return array<int,string> */
    protected function lineas(?string $texto): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $texto))));
    }
}
