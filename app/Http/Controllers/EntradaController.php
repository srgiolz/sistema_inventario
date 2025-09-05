<?php

namespace App\Http\Controllers;

use App\Models\Entrada;
use App\Models\DetalleEntrada;
use App\Models\Producto;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\InventarioService;

class EntradaController extends Controller
{
    public function index()
    {
        $entradas = Entrada::with(['sucursal', 'detalles.producto'])->latest()->get();
        return view('entradas.index', compact('entradas'));
    }

    public function create()
    {
        $productos = Producto::all();
        $sucursales = Sucursal::all();
        return view('entradas.create', compact('productos', 'sucursales'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sucursal_id' => 'required|exists:sucursales,id',
            'fecha' => 'required|date',
            'tipo' => 'required|string|max:100',
            'observacion' => 'nullable|string|max:255',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio_unitario' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $entrada = Entrada::create([
                'sucursal_id' => $request->sucursal_id,
                'fecha' => $request->fecha,
                'tipo' => $request->tipo,
                'observacion' => $request->observacion,
                'estado' => 'pendiente',
            ]);

            foreach ($request->productos as $producto) {
                DetalleEntrada::create([
                    'entrada_id' => $entrada->id,
                    'producto_id' => $producto['producto_id'],
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $producto['precio_unitario'] ?? 0,
                ]);
            }
        });

        return redirect()->route('entradas.index')->with('success', 'Entrada registrada en estado PENDIENTE.');
    }

    public function edit($id)
    {
        $entrada = Entrada::with('detalles')->findOrFail($id);

        if ($entrada->estado !== 'pendiente') {
            return redirect()->route('entradas.index')->with('error', 'Solo se pueden editar entradas en estado PENDIENTE.');
        }

        $productos = Producto::all();
        $sucursales = Sucursal::all();

        return view('entradas.edit', compact('entrada', 'productos', 'sucursales'));
    }

    public function update(Request $request, $id)
    {
        $entrada = Entrada::with('detalles')->findOrFail($id);

        if ($entrada->estado !== 'pendiente') {
            return redirect()->route('entradas.index')->with('error', 'Esta entrada ya no puede modificarse.');
        }

        $request->validate([
            'fecha' => 'required|date',
            'tipo' => 'required|string|max:100',
            'observacion' => 'nullable|string|max:255',
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|numeric|min:1',
            'productos.*.precio_unitario' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $entrada) {
    $entrada->update([
        'fecha' => $request->fecha,
        'tipo' => $request->tipo,
        'observacion' => $request->observacion,
        'sucursal_id' => $request->sucursal_id, // 🔹 AÑADIR ESTO
    ]);

    $entrada->detalles()->delete();

    foreach ($request->productos as $producto) {
        $entrada->detalles()->create([
            'producto_id' => $producto['producto_id'],
            'cantidad' => $producto['cantidad'],
            'precio_unitario' => $producto['precio_unitario'] ?? 0,
        ]);
    }
});


        return redirect()->route('entradas.index')->with('success', 'Entrada actualizada correctamente.');
    }

    public function confirmar($id)
    {
        $entrada = Entrada::with('detalles')->findOrFail($id);

        if ($entrada->estado !== 'pendiente') {
            return back()->withErrors('Solo se pueden confirmar entradas en estado PENDIENTE.');
        }

        DB::transaction(function () use ($entrada) {
            foreach ($entrada->detalles as $detalle) {
                InventarioService::entradaNormal(
                    $entrada->sucursal_id,
                    $detalle->producto_id,
                    $detalle->cantidad,
                    $detalle->precio_unitario ?? 0,
                    'ENTRADA',
                    $entrada->id,
                    auth()->id()
                );
            }

            $entrada->update([
                'estado' => 'confirmado',
                'fecha_confirmacion' => now(),
                'usuario_confirma_id' => auth()->id(),
            ]);
        });

        return redirect()->route('entradas.index')->with('success', 'Entrada confirmada correctamente.');
    }

    public function anular(Request $request, $id)
    {
        $entrada = Entrada::with('detalles')->findOrFail($id);

        if ($entrada->estado !== 'confirmado') {
            return back()->withErrors('Solo se pueden anular entradas en estado CONFIRMADO.');
        }

        $request->validate([
            'motivo' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($entrada, $request) {
            foreach ($entrada->detalles as $detalle) {
    InventarioService::anulacion(
        $entrada->sucursal_id,
        $detalle->producto_id,
        $detalle->cantidad,
        $detalle->precio_unitario ?? 0,
        'ENTRADA',   // 🔹 documento original
        $entrada->id,
        auth()->id(),
        $request->motivo
    );
}

            $entrada->update([
                'estado' => 'anulado',
                'motivo_anulacion' => $request->motivo,
            ]);
        });

        return redirect()->route('entradas.index')->with('success', 'Entrada anulada correctamente.');
    }

    public function generarPdf($id)
    {
        $entrada = Entrada::with(['sucursal', 'detalles.producto'])->findOrFail($id);
        $pdf = Pdf::loadView('entradas.pdf', compact('entrada'))->setPaper('A4', 'portrait');
        return $pdf->stream('Entrada_' . $entrada->id . '.pdf');
    }
}


