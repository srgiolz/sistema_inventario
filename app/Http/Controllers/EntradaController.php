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

        $idsReversadas = Entrada::where('observacion', 'like', 'Reversión de entrada #%')
            ->pluck('observacion')
            ->map(fn($obs) => (int) filter_var($obs, FILTER_SANITIZE_NUMBER_INT))
            ->toArray();

        return view('entradas.index', compact('entradas', 'idsReversadas'));
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
            ]);

            foreach ($request->productos as $producto) {
                DetalleEntrada::create([
                    'entrada_id' => $entrada->id,
                    'producto_id' => $producto['producto_id'],
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $producto['precio_unitario'] ?? null,
                ]);

                // Usar el servicio para mover stock y registrar Kardex
                InventarioService::entradaNormal(
                    $request->sucursal_id,
                    $producto['producto_id'],
                    $producto['cantidad'],
                    $producto['precio_unitario'] ?? 0,
                    'Entrada',
                    $entrada->id,
                    auth()->id()
                );
            }
        });

        return redirect()->route('entradas.index')->with('success', 'Entrada registrada correctamente.');
    }

    public function reversar($id)
    {
        $entradaOriginal = Entrada::with('detalles.producto')->findOrFail($id);

        // Validar que haya stock suficiente para revertir
        foreach ($entradaOriginal->detalles as $detalle) {
            $stockActual = \App\Models\Inventario::where('producto_id', $detalle->producto_id)
                ->where('sucursal_id', $entradaOriginal->sucursal_id)
                ->value('cantidad') ?? 0;

            if ($stockActual < $detalle->cantidad) {
                return redirect()->route('entradas.index')
                    ->with('error', 'No se puede reversar: stock insuficiente del producto "' . $detalle->producto->descripcion . '"');
            }
        }

        DB::transaction(function () use ($entradaOriginal) {
            $entradaReversa = Entrada::create([
                'sucursal_id' => $entradaOriginal->sucursal_id,
                'fecha' => now()->format('Y-m-d'),
                'tipo' => $entradaOriginal->tipo,
                'observacion' => 'Reversión de entrada #' . $entradaOriginal->id,
            ]);

            foreach ($entradaOriginal->detalles as $detalle) {
                DetalleEntrada::create([
                    'entrada_id' => $entradaReversa->id,
                    'producto_id' => $detalle->producto_id,
                    'cantidad' => -$detalle->cantidad,
                    'precio_unitario' => $detalle->precio_unitario,
                ]);

                // Usar el servicio para restar stock
                InventarioService::salidaNormal(
    $entradaOriginal->sucursal_id,
    $detalle->producto_id,
    $detalle->cantidad,
    $detalle->precio_unitario ?? 0,
    'Reversión Entrada',
    $entradaReversa->id,
    auth()->id(),
    'Reversión de entrada #' . $entradaOriginal->id
);
            }
        });

        return redirect()->route('entradas.index')->with('success', 'Entrada reversada correctamente.');
    }

    public function edit($id)
    {
        $entrada = Entrada::with('detalles')->findOrFail($id);

        if (!\Carbon\Carbon::parse($entrada->fecha)->isToday()) {
            return redirect()->route('entradas.index')->with('error', 'Solo puedes editar entradas del mismo día.');
        }

        $productos = Producto::all();
        $sucursales = Sucursal::all();

        return view('entradas.edit', compact('entrada', 'productos', 'sucursales'));
    }

    public function update(Request $request, $id)
    {
        $entrada = Entrada::findOrFail($id);

        if (!\Carbon\Carbon::parse($entrada->fecha)->isToday()) {
            return redirect()->route('entradas.index')->with('error', 'Solo se pueden editar entradas del mismo día.');
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

        $productosIds = collect($request->productos)->pluck('producto_id');
        if ($productosIds->duplicates()->isNotEmpty()) {
            return back()->withErrors(['productos' => 'No se permiten productos duplicados.'])->withInput();
        }

        DB::transaction(function () use ($request, $entrada) {
            $entrada->update([
                'fecha' => $request->fecha,
                'tipo' => $request->tipo,
                'observacion' => $request->observacion,
            ]);

            // Eliminar detalles antiguos y ajustar inventario (reversa de lo anterior)
            foreach ($entrada->detalles as $detalle) {
                InventarioService::salidaNormal(
                    $entrada->sucursal_id,
                    $detalle->producto_id,
                    $detalle->cantidad,
                    $detalle->precio_unitario ?? 0,
                    'Edición Entrada',
                    $entrada->id,
                    auth()->id()
                );
                $detalle->delete();
            }

            // Insertar nuevos detalles y sumar al inventario
            foreach ($request->productos as $producto) {
                $entrada->detalles()->create([
                    'producto_id' => $producto['producto_id'],
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $producto['precio_unitario'] ?? 0,
                ]);

                InventarioService::entradaNormal(
                    $entrada->sucursal_id,
                    $producto['producto_id'],
                    $producto['cantidad'],
                    $producto['precio_unitario'] ?? 0,
                    'Edición Entrada',
                    $entrada->id,
                    auth()->id()
                );
            }
        });

        return redirect()->route('entradas.index')->with('success', 'Entrada actualizada correctamente.');
    }

    public function generarPdf($id)
    {
        $entrada = Entrada::with(['sucursal', 'detalles.producto'])->findOrFail($id);
        $pdf = Pdf::loadView('entradas.pdf', compact('entrada'))->setPaper('A4', 'portrait');
        return $pdf->stream('Entrada_' . $entrada->id . '.pdf');
    }
}


