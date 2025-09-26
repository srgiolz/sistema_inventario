<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\EntradaController;
use App\Http\Controllers\SalidaController;
use App\Http\Controllers\TraspasoController;
use App\Http\Controllers\PanelDecisionesController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\KardexController;

// Rutas protegidas por auth
Route::middleware(['auth'])->group(function () {
    
    // 🏠 Panel principal
    Route::get('/', [PanelController::class, 'index'])->name('panel.index');
    Route::get('/panel/filtrar-productos', [PanelController::class, 'filtrarProductosSinVentas'])->name('panel.filtrar-productos');
    Route::get('/panel/filtrar-ventas-tipo', [PanelController::class, 'filtrarVentasPorTipo'])->name('panel.filtrar-ventas-tipo');
    Route::get('/panel/filtrar-movimientos', [PanelController::class, 'filtrarMovimientos'])->name('panel.filtrar-movimientos');

    // 📦 Inventario - Productos / Clientes / Ventas
    Route::get('/productos/inventario', [ProductoController::class, 'inventario'])->name('productos.inventario');
    Route::resource('productos', ProductoController::class);
    Route::resource('clientes', ClienteController::class);
    Route::resource('ventas', VentaController::class);
    Route::patch('/ventas/{id}/anular', [VentaController::class, 'anular'])->name('ventas.anular');
    Route::get('/ventas/{venta}/ticket', [VentaController::class, 'generarTicket'])->name('ventas.ticket');

    // Inventario/Entradas
    Route::resource('entradas', EntradaController::class);
    Route::post('/entradas/{id}/confirmar', [EntradaController::class, 'confirmar'])->name('entradas.confirmar');
    Route::post('/entradas/{id}/anular', [EntradaController::class, 'anular'])->name('entradas.anular');
    Route::get('/entradas/{id}/pdf', [EntradaController::class, 'generarPdf'])->name('entradas.pdf');

    // Inventario/Salidas
    Route::resource('salidas', SalidaController::class);
    Route::post('/salidas/{id}/confirmar', [SalidaController::class, 'confirm'])->name('salidas.confirm');
    Route::post('/salidas/{id}/anular', [SalidaController::class, 'anular'])->name('salidas.anular');
    Route::get('/salidas/{id}/pdf', [SalidaController::class, 'generarPdf'])->name('salidas.pdf');

    // 📦 Traspasos
    Route::prefix('traspasos')->group(function () {
        // Listado y PDF
        Route::get('/', [TraspasoController::class, 'index'])->name('traspasos.index');
        Route::get('/{traspaso}/pdf', [TraspasoController::class, 'generarPDF'])->name('traspasos.pdf');

        // Revisar (detalle + botones de acción)
        Route::get('/{traspaso}/revisar', [TraspasoController::class, 'revisar'])->name('traspasos.revisar');

        // Crear / Guardar
        Route::get('/create', [TraspasoController::class, 'create'])->name('traspasos.create');
        Route::post('/', [TraspasoController::class, 'store'])->name('traspasos.store');

        // Editar / Actualizar (solo si está pendiente)
        Route::get('/{traspaso}/edit', [TraspasoController::class, 'edit'])->name('traspasos.edit');
        Route::put('/{traspaso}', [TraspasoController::class, 'update'])->name('traspasos.update');

        // Confirmaciones
        Route::post('/{traspaso}/confirmar-origen', [TraspasoController::class, 'confirmarOrigen'])->name('traspasos.confirmarOrigen');
        Route::post('/{traspaso}/confirmar-destino', [TraspasoController::class, 'confirmarDestino'])->name('traspasos.confirmarDestino');

        // Rechazar / Anular
        Route::post('/{traspaso}/rechazar', [TraspasoController::class, 'rechazar'])->name('traspasos.rechazar');
        Route::post('/{traspaso}/anular', [TraspasoController::class, 'anular'])->name('traspasos.anular');
    });

    // 📊 Panel decisiones
    Route::get('/panel-decisiones', [PanelDecisionesController::class, 'index'])->name('panel-decisiones');

    // 👤 Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 📒 Kardex
    Route::get('/kardex', [KardexController::class, 'index'])->name('kardex.index');

    // 📈 API general de productos (select2)
    Route::get('/api/productos', function () {
        $productos = \App\Models\Producto::select('id', 'item_codigo', 'descripcion')->get();
        return response()->json(
            $productos->map(fn($p) => [
                'id' => $p->id,
                'text' => "{$p->item_codigo} - {$p->descripcion}"
            ])
        );
    });

    // 📦 API productos para traspasos (fuera del prefix para evitar conflictos)
    Route::get('/api/productos-por-sucursal/{idSucursal}', [TraspasoController::class, 'productosPorSucursal'])
        ->name('api.productosPorSucursal');

    // 📊 Stock individual (fuera del prefix, como antes)
    Route::get('/stock/{id_producto}/{id_sucursal}', [TraspasoController::class, 'obtenerStock']);
});

// 🔐 Auth
require __DIR__.'/auth.php';
