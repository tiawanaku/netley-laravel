<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Catalogos\CatalogoController;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Admin\ConsultaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FinanzasController;
use App\Http\Controllers\Admin\GastoController;
use App\Http\Controllers\Admin\PersonalController;
use App\Http\Controllers\Admin\PlanPagoController;
use App\Http\Controllers\Admin\ProcesoController;
use App\Http\Controllers\Admin\ProcesoDocumentoController;
use App\Http\Controllers\Admin\ReciboController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:web')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store']);
    });

    Route::middleware('auth:web')->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('agenda/eventos', [DashboardController::class, 'eventos'])->name('agenda.eventos');

        Route::post('personal/{personal}/resetear-acceso', [PersonalController::class, 'resetAcceso'])
            ->name('personal.reset-acceso');
        Route::resource('personal', PersonalController::class)->except(['show']);

        Route::get('consultas/horarios-disponibles', [ConsultaController::class, 'horariosDisponibles'])
            ->name('consultas.horarios-disponibles');
        Route::post('consultas/{consulta}/agendar-cita', [ConsultaController::class, 'agendarCita'])
            ->name('consultas.agendar-cita');
        Route::post('consultas/{consulta}/agendar-llamada', [ConsultaController::class, 'agendarLlamada'])
            ->name('consultas.agendar-llamada');
        Route::post('consultas/{consulta}/dar-respuesta', [ConsultaController::class, 'darRespuesta'])
            ->name('consultas.dar-respuesta');
        Route::post('consultas/{consulta}/convertir', [ConsultaController::class, 'convertir'])
            ->name('consultas.convertir');
        Route::resource('consultas', ConsultaController::class);

        Route::get('clientes/delitos-por-materia', [ClienteController::class, 'delitosPorMateria'])
            ->name('clientes.delitos-por-materia');
        Route::get('clientes/abogados-por-materia', [ClienteController::class, 'abogadosPorMateria'])
            ->name('clientes.abogados-por-materia');
        Route::resource('clientes', ClienteController::class)->except(['destroy']);

        Route::get('clientes/{cliente}/casos/crear', [ProcesoController::class, 'create'])->name('procesos.create');
        Route::post('clientes/{cliente}/casos', [ProcesoController::class, 'store'])->name('procesos.store');
        Route::get('casos/{proceso}', [ProcesoController::class, 'show'])->name('procesos.show');
        Route::get('casos/{proceso}/editar', [ProcesoController::class, 'edit'])->name('procesos.edit');
        Route::put('casos/{proceso}', [ProcesoController::class, 'update'])->name('procesos.update');
        Route::post('casos/{proceso}/plan-pagos', [ProcesoController::class, 'generarPlanPagos'])
            ->name('procesos.generar-plan-pagos');
        Route::post('casos/{proceso}/confirmar-anticipo', [ProcesoController::class, 'confirmarAnticipo'])
            ->name('procesos.confirmar-anticipo');

        Route::post('casos/{proceso}/documentos', [ProcesoDocumentoController::class, 'store'])
            ->name('procesos.documentos.store');
        Route::get('documentos/{documento}/descargar', [ProcesoDocumentoController::class, 'download'])
            ->name('documentos.descargar');
        Route::delete('documentos/{documento}', [ProcesoDocumentoController::class, 'destroy'])
            ->name('documentos.destroy');

        Route::post('cuotas/{planPago}/confirmar', [PlanPagoController::class, 'confirmar'])
            ->name('plan-pagos.confirmar');

        Route::prefix('finanzas')->name('finanzas.')->group(function () {
            Route::get('/', [FinanzasController::class, 'index'])->name('index');

            Route::get('recibos', [ReciboController::class, 'index'])->name('recibos.index');
            Route::get('recibos/crear', [ReciboController::class, 'create'])->name('recibos.create');
            Route::post('recibos', [ReciboController::class, 'store'])->name('recibos.store');
            Route::get('recibos/{recibo}', [ReciboController::class, 'show'])->name('recibos.show');

            Route::get('gastos', [GastoController::class, 'index'])->name('gastos.index');
            Route::get('gastos/crear', [GastoController::class, 'create'])->name('gastos.create');
            Route::post('gastos', [GastoController::class, 'store'])->name('gastos.store');
            Route::get('gastos/{gasto}/comprobante', [GastoController::class, 'descargarComprobante'])
                ->name('gastos.comprobante');
            Route::delete('gastos/{gasto}', [GastoController::class, 'destroy'])->name('gastos.destroy');
        });

        Route::prefix('catalogos/{catalogo}')->name('catalogos.')->group(function () {
            Route::get('/', [CatalogoController::class, 'index'])->name('index');
            Route::get('crear', [CatalogoController::class, 'create'])->name('create');
            Route::post('/', [CatalogoController::class, 'store'])->name('store');
            Route::get('{registro}/editar', [CatalogoController::class, 'edit'])->name('edit');
            Route::put('{registro}', [CatalogoController::class, 'update'])->name('update');
            Route::delete('{registro}', [CatalogoController::class, 'destroy'])->name('destroy');
        });
    });
});
