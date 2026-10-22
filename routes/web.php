<?php

use App\Http\Controllers\AptoFisicoController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\CuentaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MembresiaController;
use App\Http\Controllers\OperadorController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\Portal\AforoController;
use App\Http\Controllers\Portal\AuthSocioController;
use App\Http\Controllers\Portal\HistorialController;
use App\Http\Controllers\Portal\MisDatosController;
use App\Http\Controllers\Portal\PortalController;
use App\Http\Controllers\Portal\PreguntasController;
use App\Http\Controllers\Portal\RutinaSocioController;
use App\Http\Controllers\PreguntaFrecuenteController;
use App\Http\Controllers\RecepcionController;
use App\Http\Controllers\RutinaController;
use App\Http\Controllers\SocioController;
use App\Http\Controllers\SolicitudDatosController;
use App\Http\Controllers\WebController;
use App\Livewire\PantallaAcceso;
use App\Livewire\SociosIndex;
use Illuminate\Support\Facades\Route;

// Página web pública del gimnasio (los operadores con sesión son redirigidos a su panel).
Route::get('/', [WebController::class, 'inicio'])->name('web');
Route::get('/privacidad', [WebController::class, 'privacidad'])->name('privacidad');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});

Route::middleware(['auth', 'rol:administrador,recepcionista,profesor,terminal'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/panel', fn () => redirect()->route(auth()->user()->rutaInicio()))->name('inicio');
    // Mi contraseña (HU30): cada operador cambia la suya; se limita para que no se pueda adivinar la actual.
    Route::get('/mi-clave', [CuentaController::class, 'editarClave'])->name('cuenta.clave');
    Route::put('/mi-clave', [CuentaController::class, 'actualizarClave'])->name('cuenta.clave.update')->middleware('throttle:5,1');
});

// Pantalla de acceso (ingreso / egreso por PIN o QR). La terminal de la entrada (HU31) solo puede abrir esta pantalla.
Route::get('/acceso', PantallaAcceso::class)->name('acceso')->middleware(['auth', 'rol:administrador,recepcionista,terminal']);

// Rutinas: las arma un profesor (o el administrador) a pedido del socio.
Route::middleware(['auth', 'rol:administrador,profesor'])->group(function () {
    Route::get('/rutinas', [RutinaController::class, 'index'])->name('rutinas.index');
    Route::get('/rutinas/plantillas', [RutinaController::class, 'plantillas'])->name('rutinas.plantillas');
    Route::get('/rutinas/plantillas/{codigo}', [RutinaController::class, 'plantilla'])->name('rutinas.plantilla');
    Route::get('/rutinas/pedidos/{solicitud}/armar', [RutinaController::class, 'create'])->name('rutinas.create');
    Route::post('/rutinas/pedidos/{solicitud}', [RutinaController::class, 'store'])->name('rutinas.store');
    Route::get('/rutinas/{rutina}/editar', [RutinaController::class, 'edit'])->name('rutinas.edit');
    Route::put('/rutinas/{rutina}', [RutinaController::class, 'update'])->name('rutinas.update');
});

Route::middleware(['auth', 'rol:administrador,recepcionista'])->group(function () {

    // Panel de recepción: inicio del recepcionista (HU29)
    Route::get('/recepcion', [RecepcionController::class, 'index'])->name('recepcion');

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
        Route::resource('preguntas', PreguntaFrecuenteController::class)->except(['show']);
        Route::get('/configuracion', [ConfiguracionController::class, 'edit'])->name('configuracion.edit');
        Route::put('/configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');
        Route::get('/solicitudes-datos', [SolicitudDatosController::class, 'index'])->name('solicitudes-datos.index');
        Route::put('/solicitudes-datos/{solicitud}', [SolicitudDatosController::class, 'resolver'])->name('solicitudes-datos.resolver');
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
        Route::get('/asistencias', [HistorialController::class, 'asistencias'])->name('asistencias');
        Route::get('/pagos', [HistorialController::class, 'pagos'])->name('pagos');
        Route::get('/pagos/{pago}/comprobante', [HistorialController::class, 'comprobante'])->name('pagos.comprobante');
        Route::get('/apto', [HistorialController::class, 'aptos'])->name('aptos');
        Route::get('/apto/{apto}/archivo', [HistorialController::class, 'aptoArchivo'])->name('aptos.archivo');
        Route::get('/rutina', [RutinaSocioController::class, 'show'])->name('rutina');
        Route::post('/rutina/pedido', [RutinaSocioController::class, 'solicitar'])->name('rutina.solicitar');
        Route::get('/aforo', [AforoController::class, 'index'])->name('aforo');
        Route::get('/preguntas', [PreguntasController::class, 'index'])->name('preguntas');
        Route::get('/mis-datos', [MisDatosController::class, 'show'])->name('mis-datos');
        Route::post('/mis-datos/solicitud', [MisDatosController::class, 'solicitar'])->name('mis-datos.solicitar');
    });
});
