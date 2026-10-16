<?php

namespace App\Providers;

use App\Http\Middleware\SocioAutenticado;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
