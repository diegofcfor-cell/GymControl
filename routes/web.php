<?php

use App\Http\Controllers\AptoFisicoController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MembresiaController;
use App\Http\Controllers\OperadorController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\Portal\AforoController;
use App\Http\Controllers\Portal\AuthSocioController;
use App\Http\Controllers\Portal\PortalController;
use App\Http\Controllers\SocioController;
use App\Livewire\PantallaAcceso;
use App\Livewire\SociosIndex;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});

Route::middleware(['auth', 'rol:administrador,recepcionista'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', fn () => auth()->user()->esAdministrador()
        ? redirect()->route('dashboard')
        : redirect()->route('acceso'))->name('inicio');

    // Pantalla de acceso (ingreso / egreso por PIN o QR)
    Route::get('/acceso', PantallaAcceso::class)->name('acceso');

    // Socios
    Route::get('/socios', SociosIndex::class)->name('socios.index');
    Route::get('/socios/crear', [SocioController::class, 'create'])->name('socios.create');
    Route::post('/socios', [SocioController::class, 'store'])->name('socios.store');
    Route::get('/socios/{socio}', [SocioController::class, 'show'])->name('socios.show')->withTrashed();
    Route::get('/socios/{socio}/editar', [SocioController::class, 'edit'])->name('socios.edit');
    Route::put('/socios/{socio}', [SocioController::class, 'update'])->name('socios.update');
    Route::get('/socios/{socio}/credencial', [SocioController::class, 'credencial'])->name('socios.credencial');
    Route::get('/socios/{socio}/ficha.pdf', [SocioController::class, 'fichaPdf'])->name('socios.ficha');

    // Aptos físicos
    Route::post('/socios/{socio}/aptos', [AptoFisicoController::class, 'store'])->name('aptos.store');
    Route::get('/aptos/{apto}/archivo', [AptoFisicoController::class, 'archivo'])->name('aptos.archivo');

    // Pagos
    Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');
    Route::get('/pagos/pdf', [PagoController::class, 'pdf'])->name('pagos.pdf');
    Route::get('/socios/{socio}/pagos/crear', [PagoController::class, 'create'])->name('pagos.create');
    Route::post('/socios/{socio}/pagos', [PagoController::class, 'store'])->name('pagos.store');

    // Historial de asistencias
    Route::get('/asistencias', [AsistenciaController::class, 'index'])->name('asistencias.index');
    Route::get('/asistencias/pdf', [AsistenciaController::class, 'pdf'])->name('asistencias.pdf');

    // Solo administrador
    Route::middleware('rol:administrador')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::delete('/socios/{socio}', [SocioController::class, 'destroy'])->name('socios.destroy');
        Route::post('/socios/{socio}/reactivar', [SocioController::class, 'restore'])->name('socios.restore')->withTrashed();
        Route::resource('membresias', MembresiaController::class)->except(['show', 'destroy']);
        Route::get('/configuracion', [ConfiguracionController::class, 'edit'])->name('configuracion.edit');
        Route::put('/configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');
        Route::resource('operadores', OperadorController::class)->parameters(['operadores' => 'operador'])->except(['show', 'destroy']);
    });
});

// Portal del socio: sesión propia (PIN + DNI), separada del login de operadores.
Route::prefix('socio')->name('portal.')->group(function () {
    Route::get('/login', [AuthSocioController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthSocioController::class, 'login'])->middleware('throttle:10,1');

    Route::middleware('socio')->group(function () {
        Route::post('/logout', [AuthSocioController::class, 'logout'])->name('logout');
        Route::get('/', [PortalController::class, 'inicio'])->name('inicio');
        Route::get('/aforo', [AforoController::class, 'index'])->name('aforo');
    });
});
