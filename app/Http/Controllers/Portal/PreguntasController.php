<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\PreguntaFrecuente;

class PreguntasController extends Controller
{
    public function index()
    {
        return view('portal.preguntas', ['preguntas' => PreguntaFrecuente::activas()->get()]);
    }
}
