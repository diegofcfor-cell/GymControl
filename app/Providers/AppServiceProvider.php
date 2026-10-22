<?php

namespace App\Providers;

use App\Http\Middleware\SocioAutenticado;
use App\Services\AccesoService;
use App\Services\ReglasIngreso\AptoFisicoVigente;
use App\Services\ReglasIngreso\CuotaVigente;
use App\Services\ReglasIngreso\ReglaIngreso;
use App\Services\ReglasIngreso\SocioActivo;
use App\Services\ReglasIngreso\TopeHorasDiarias;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Reglas de ingreso (Strategy), en el orden en que se muestran los motivos de rechazo.
        $this->app->when(AccesoService::class)->needs(ReglaIngreso::class)->give([
            SocioActivo::class,
            CuotaVigente::class,
            AptoFisicoVigente::class,
            TopeHorasDiarias::class,
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Las acciones Livewire del portal (chatbot, aforo) vuelven a exigir la sesión del socio.
        Livewire::addPersistentMiddleware([SocioAutenticado::class]);
    }
}
