<?php

namespace App\Http\Controllers;

use App\Models\Salida;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DetalleSalida;


class SalidaController extends Controller
{
    public function create()
    {
        $productos = Producto::all();
        $sucursales = Sucursal::all();
        return view('salidas.create', compact('productos', 'sucursales'));
    }
public function store(Request $request)
{
    $request->validate([
        'fecha' => 'required|date',
        'id_sucursal' => 'required|exists:sucursales,id',
        'tipo' => 'required|string|max:255',
        'motivo' => 'required|string|max:255',
        'observacion' => 'nullable|string|max:255',
        'productos' => 'required|array|min:1',
        'productos.*.id_producto' => 'required|exists:productos,id',
        'productos.*.cantidad' => 'required|integer|min:1',
    ]);

    try {
        DB::beginTransaction();

        // 1️⃣ Crear la cabecera de salida
        $salida = Salida::create([
            'fecha' => $request->fecha,
            'id_sucursal' => $request->id_sucursal,
            'tipo' => $request->tipo,
            'motivo' => $request->motivo,
            'observacion' => $request->observacion,
        ]);

        // 2️⃣ Recorrer productos para detalle y stock
        foreach ($request->productos as $item) {
            $productoId = $item['id_producto'];
            $cantidad = $item['cantidad'];

            $inventario = Inventario::where('id_producto', $productoId)
                ->where('id_sucursal', $request->id_sucursal)
                ->first();

            if (!$inventario || $inventario->cantidad < $cantidad) {
                DB::rollBack();
                return redirect()->back()
                    ->withErrors(['stock' => "Stock insuficiente para el producto ID: $productoId"])
                    ->withInput();
            }

            // Guardar el detalle
            DetalleSalida::create([
                'salida_id' => $salida->id,
                'id_producto' => $productoId,
                'cantidad' => $cantidad,
            ]);

            // Descontar del inventario
            $inventario->cantidad -= $cantidad;
            $inventario->save();
        }

        DB::commit();

        return redirect()->route('salidas.index')->with('success', 'Salida registrada exitosamente.');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()
            ->withErrors(['error' => 'Error al registrar la salida: ' . $e->getMessage()])
            ->withInput();
    }
}
    public function index()
    {
    $salidas = Salida::with('detalles.producto', 'sucursal')->latest()->get();
    return view('salidas.index', compact('salidas'));
    }

}
