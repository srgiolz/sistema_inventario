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

    // 📦 Inventario
    Route::get('/productos/inventario', [ProductoController::class, 'inventario'])->name('productos.inventario');
    Route::resource('productos', ProductoController::class);
    Route::resource('clientes', ClienteController::class);
    Route::resource('ventas', VentaController::class);
    //Inventario/Entradas
    Route::resource('entradas', EntradaController::class);
    Route::post('/entradas/{id}/reversar', [EntradaController::class, 'reversar'])->name('entradas.reversar');
    Route::get('/entradas/{id}/editar', [EntradaController::class, 'edit'])->name('entradas.edit');
    Route::put('entradas/{id}', [EntradaController::class, 'update'])->name('entradas.update');
    Route::get('/entradas/{id}/pdf', [EntradaController::class, 'generarPdf'])->name('entradas.pdf');

    //Inventario/Salidas
    Route::resource('salidas', SalidaController::class);
    Route::get('/salidas/{id}/pdf', [\App\Http\Controllers\SalidaController::class, 'generarPdf'])->name('salidas.pdf');
    Route::post('/salidas/{id}/reversar', [\App\Http\Controllers\SalidaController::class, 'reversar'])->name('salidas.reversar');
    Route::get('/salidas/{id}/edit', [\App\Http\Controllers\SalidaController::class, 'edit'])->name('salidas.edit');
    Route::put('/salidas/{id}', [\App\Http\Controllers\SalidaController::class, 'update'])->name('salidas.update');

    
    Route::resource('traspasos', TraspasoController::class);
    Route::get('/api/productos-por-sucursal/{idSucursal}', [TraspasoController::class, 'productosPorSucursal'])
    ->name('api.productosPorSucursal');

    // 📄 PDF y revisión de traspasos
    Route::get('/traspasos/{traspaso}/pdf', [TraspasoController::class, 'generarPDF'])->name('traspasos.pdf');
    Route::get('/traspasos/{traspaso}/revisar', [TraspasoController::class, 'revisar'])->name('traspasos.revisar');
    Route::patch('/traspasos/{traspaso}/confirmar', [TraspasoController::class, 'confirmar'])->name('traspasos.confirmar');
    Route::patch('/traspasos/{traspaso}/rechazar', [TraspasoController::class, 'rechazar'])->name('traspasos.rechazar');

    // 🔄 Editar traspasos
    Route::get('/traspasos/{id}/editar', [TraspasoController::class, 'edit'])->name('traspasos.edit');
    Route::put('/traspasos/{id}', [TraspasoController::class, 'update'])->name('traspasos.update');

    // 📊 Panel decisiones
    Route::get('/panel-decisiones', [PanelDecisionesController::class, 'index'])->name('panel-decisiones');

    // 👤 Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 🧾 Ticket venta
    Route::get('/ventas/{venta}/ticket', [VentaController::class, 'generarTicket'])->name('ventas.ticket');

    // 📒 Kardex
    Route::get('/kardex', [KardexController::class, 'index'])->name('kardex.index');

    // 📈 Stock individual (para JS)
    Route::get('/stock/{id_producto}/{id_sucursal}', [TraspasoController::class, 'obtenerStock']);
    Route::get('/api/productos/{id}/stock', [ProductoController::class, 'stock']);

    // 🔍 Select2 para ventas y traspasos (USAR SOLO ESTE)
    Route::get('/api/productos', function () {
        $productos = \App\Models\Producto::select('id', 'item_codigo', 'descripcion')->get();

        return response()->json(
            $productos->map(function ($p) {
                return [
                    'id' => $p->id,
                    'text' => "{$p->item_codigo} - {$p->descripcion}"
                ];
            })
        );
    });
});

// 🔐 Auth
require __DIR__.'/auth.php';
