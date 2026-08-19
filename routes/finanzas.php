<?php

use App\Http\Controllers\Finanzas\CobranzaController;
use App\Http\Controllers\Finanzas\CuentaCorrienteProveedorController;
use App\Http\Controllers\Finanzas\EnvioController;
use App\Http\Controllers\Finanzas\FinanzasDashboardController;
use App\Http\Controllers\Finanzas\GastoCategoriaController;
use App\Http\Controllers\Finanzas\GastoController;
use App\Http\Controllers\Finanzas\ReposicionController;
use App\Http\Controllers\Finanzas\TransportistaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas del módulo Finanzas
|--------------------------------------------------------------------------
| Este archivo se incluye desde routes/tenant-routes.php (lo hace el proceso
| principal). No define middleware de grupo web propio: solo auth + verified.
*/

Route::middleware(['auth', 'verified'])->prefix('finanzas')->name('finanzas.')->group(function () {

    // Tablero
    Route::get('/', [FinanzasDashboardController::class, 'index'])->name('dashboard');

    // Gastos
    Route::get('gastos', [GastoController::class, 'index'])->name('gastos.index');
    Route::get('gastos/list', [GastoController::class, 'list'])->name('gastos.list');
    Route::post('gastos', [GastoController::class, 'store'])->name('gastos.store');
    Route::get('gastos/{id}', [GastoController::class, 'show'])->name('gastos.show');
    Route::post('gastos/{id}/update', [GastoController::class, 'update'])->name('gastos.update');
    Route::delete('gastos/{id}', [GastoController::class, 'destroy'])->name('gastos.destroy');
    Route::post('gastos/{id}/registrar-pago', [GastoController::class, 'registrarPago'])->name('gastos.pagar');

    // Categorías de gastos (AJAX desde el index de gastos)
    Route::get('gasto-categorias', [GastoCategoriaController::class, 'index'])->name('categorias.index');
    Route::post('gasto-categorias', [GastoCategoriaController::class, 'store'])->name('categorias.store');
    Route::post('gasto-categorias/{id}/update', [GastoCategoriaController::class, 'update'])->name('categorias.update');
    Route::delete('gasto-categorias/{id}', [GastoCategoriaController::class, 'destroy'])->name('categorias.destroy');

    // Transportistas
    Route::get('transportistas', [TransportistaController::class, 'index'])->name('transportistas.index');
    Route::get('transportistas/list', [TransportistaController::class, 'list'])->name('transportistas.list');
    Route::post('transportistas', [TransportistaController::class, 'store'])->name('transportistas.store');
    Route::post('transportistas/{id}/update', [TransportistaController::class, 'update'])->name('transportistas.update');
    Route::delete('transportistas/{id}', [TransportistaController::class, 'destroy'])->name('transportistas.destroy');

    // Envíos y fletes
    Route::get('envios', [EnvioController::class, 'index'])->name('envios.index');
    Route::get('envios/list', [EnvioController::class, 'list'])->name('envios.list');
    Route::post('envios', [EnvioController::class, 'store'])->name('envios.store');
    Route::post('envios/{id}/update', [EnvioController::class, 'update'])->name('envios.update');
    Route::post('envios/{id}/estado', [EnvioController::class, 'setEstado'])->name('envios.estado');
    Route::delete('envios/{id}', [EnvioController::class, 'destroy'])->name('envios.destroy');

    // Cuentas por pagar (cuenta corriente de proveedores)
    Route::get('cxp', [CuentaCorrienteProveedorController::class, 'index'])->name('cxp.index');
    Route::get('cxp/{proveedorId}', [CuentaCorrienteProveedorController::class, 'show'])->name('cxp.show');
    Route::post('cxp/{proveedorId}/registrar-pago', [CuentaCorrienteProveedorController::class, 'registrarPago'])->name('cxp.pagar');
    Route::post('cxp/{proveedorId}/registrar-ajuste', [CuentaCorrienteProveedorController::class, 'registrarAjuste'])->name('cxp.ajuste');

    // Reposición inteligente de stock
    Route::post('reposicion/generar-ahora', [ReposicionController::class, 'generarAhora'])->name('reposicion.generar-ahora');
    Route::get('reposicion/ajustes', [ReposicionController::class, 'ajustes'])->name('reposicion.ajustes');
    Route::post('reposicion/ajustes', [ReposicionController::class, 'guardarAjustes'])->name('reposicion.ajustes.guardar');

    // Cobranzas (cola de aprobación de recordatorios por deuda vencida)
    Route::get('cobranzas', [CobranzaController::class, 'index'])->name('cobranzas.index');
    Route::post('cobranzas/{id}/aprobar', [CobranzaController::class, 'aprobarYEnviar'])->name('cobranzas.aprobar');
    Route::post('cobranzas/{id}/descartar', [CobranzaController::class, 'descartar'])->name('cobranzas.descartar');
});
