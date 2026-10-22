<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Pago;
use App\Models\Socio;
use App\Repositories\AsistenciaRepository;

class DashboardController extends Controller
{
    public function index(AsistenciaRepository $asistencias)
    {
        $hoy = today();
        $inicioMes = $hoy->copy()->startOfMonth();

        $activos = Socio::where('estado', 'activo');

        $datos = [
            'sociosActivos' => (clone $activos)->conCuotaAlDia()->count(),
            'sociosVencidos' => (clone $activos)->conCuotaVencida()->count(),
            'sociosTotal' => Socio::count(),
            'presentes' => $asistencias->cantidadPresentes(),
            'asistenciasHoy' => Asistencia::whereDate('fecha', $hoy)->count(),
            'ingresosHoy' => (float) Pago::whereDate('fecha_pago', $hoy)->sum('importe'),
            'ingresosMes' => (float) Pago::whereDate('fecha_pago', '>=', $inicioMes)->sum('importe'),
            'porVencer' => Socio::where('estado', 'activo')
                ->whereDate('fecha_vencimiento', '>=', $hoy)
                ->whereDate('fecha_vencimiento', '<=', $hoy->copy()->addDays(7))->count(),
            'sinApto' => Socio::where('estado', 'activo')->whereDoesntHave('aptos', fn ($q) => $q->whereDate('fecha_vencimiento', '>=', $hoy))->count(),
            'ultimosPagos' => Pago::with(['socio', 'membresia'])->latest('id')->limit(6)->get(),
            'presentesLista' => $asistencias->presentesHoy(),
        ];

        // Asistencias por día (últimos 7 días)
        $serie = [];
        for ($i = 6; $i >= 0; $i--) {
            $dia = $hoy->copy()->subDays($i);
            $serie[] = ['dia' => $dia->translatedFormat('D d/m'), 'total' => Asistencia::whereDate('fecha', $dia)->count()];
        }
        $datos['serie'] = $serie;
        $datos['maxSerie'] = max(1, collect($serie)->max('total'));

        // Ingresos por membresía del mes
        $datos['porMembresia'] = Pago::with('membresia')->whereDate('fecha_pago', '>=', $inicioMes)->get()
            ->groupBy(fn ($p) => $p->membresia->nombre)->map(fn ($g) => ['cantidad' => $g->count(), 'total' => $g->sum('importe')]);

        return view('dashboard', $datos);
    }
}
