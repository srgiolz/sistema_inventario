<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PanelDecisionesController extends Controller
{
    public function index(Request $request){
        $stockTotalQuery = DB::table('inventarios')
           ->join('productos', 'inventarios.id_producto', '=', 'productos.id')
           ->select('productos.descripcion', DB::raw('SUM(inventarios.cantidad) as stock_total'))
           ->groupBy('productos.descripcion');

if ($request->filled('linea')) {
    $stockTotalQuery->where('productos.linea', 'like', '%' . $request->linea . '%');
}

if ($request->filled('producto')) {
    $stockTotalQuery->where('productos.descripcion', 'like', '%' . $request->producto . '%');
}

$stockTotal = $stockTotalQuery->get();


        $bajoStockQuery = DB::table('inventarios')
    ->join('productos', 'inventarios.id_producto', '=', 'productos.id')
    ->select('productos.descripcion', DB::raw('SUM(inventarios.cantidad) as stock_total'))
    ->groupBy('productos.descripcion')
    ->having('stock_total', '<', 10);

if ($request->filled('linea')) {
    $bajoStockQuery->where('productos.linea', 'like', '%' . $request->linea . '%');
}

if ($request->filled('producto')) {
    $bajoStockQuery->where('productos.descripcion', 'like', '%' . $request->producto . '%');
}

$bajoStock = $bajoStockQuery->get();

        $agotadosQuery = DB::table('inventarios')
    ->join('productos', 'inventarios.id_producto', '=', 'productos.id')
    ->select('productos.descripcion', DB::raw('SUM(inventarios.cantidad) as stock_total'))
    ->groupBy('productos.descripcion')
    ->having('stock_total', '=', 0);

if ($request->filled('linea')) {
    $agotadosQuery->where('productos.linea', 'like', '%' . $request->linea . '%');
}

if ($request->filled('producto')) {
    $agotadosQuery->where('productos.descripcion', 'like', '%' . $request->producto . '%');
}

$agotados = $agotadosQuery->get();

        $masVendidos = DB::table('detalle_ventas')
            ->join('productos', 'detalle_ventas.id_producto', '=', 'productos.id')
            ->select('productos.descripcion', DB::raw('SUM(detalle_ventas.cantidad) as total_vendido'))
            ->groupBy('productos.descripcion')
            ->orderByDesc('total_vendido')
            ->take(10)
            ->get();
        $stockGeneral = DB::table('inventarios')->sum('cantidad');

        $stockPorSucursal = DB::table('inventarios')
            ->join('sucursales', 'inventarios.id_sucursal', '=', 'sucursales.id')
            ->select('sucursales.nombre as sucursal', DB::raw('SUM(inventarios.cantidad) as total_stock'))
            ->groupBy('sucursales.nombre')
            ->get();
        $stockPorProductoSucursal = DB::table('inventarios')
            ->join('productos', 'inventarios.id_producto', '=', 'productos.id')
            ->join('sucursales', 'inventarios.id_sucursal', '=', 'sucursales.id')
            ->select(
        'productos.id as id_producto',
        'productos.descripcion',
        'sucursales.nombre as sucursal',
        'inventarios.cantidad'
    )
            ->orderBy('productos.descripcion')
            ->orderBy('sucursales.nombre')
            ->get();
$traspasosSugeridos = collect();

$productos = DB::table('productos')->get();

foreach ($productos as $producto) {
    $stockSucursales = DB::table('inventarios')
        ->join('sucursales', 'inventarios.id_sucursal', '=', 'sucursales.id')
        ->where('inventarios.id_producto', $producto->id)
        ->select('sucursales.nombre as sucursal', 'inventarios.cantidad')
        ->orderByDesc('inventarios.cantidad')
        ->get();

    if ($stockSucursales->count() < 2) continue;

    $mayor = $stockSucursales->first(); // sucursal con más stock
    $menor = $stockSucursales->last();  // sucursal con menos stock

    $diferencia = $mayor->cantidad - $menor->cantidad;

    if ($diferencia >= 3 && $menor->cantidad == 0) {
        $traspasosSugeridos->push((object)[
            'producto' => $producto->descripcion,
            'de' => $mayor->sucursal,
            'a' => $menor->sucursal,
            'sugerido' => floor($diferencia / 2)
        ]);
    }
}


        return view('panel-decisiones', compact(
    'stockTotal', 'bajoStock', 'agotados', 'masVendidos',
    'stockGeneral', 'stockPorSucursal', 'stockPorProductoSucursal',
    'traspasosSugeridos'
));


    }
}
