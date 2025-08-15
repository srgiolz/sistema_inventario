<?php

namespace App\Http\Controllers;

use App\Models\Salida;
use App\Models\Producto;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DetalleSalida;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\InventarioService;

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
            'sucursal_id' => 'required|exists:sucursales,id',
            'tipo' => 'required|string|max:255',
            'motivo' => 'required|string|max:255',
            'observacion' => 'nullable|string|max:255',
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $salida = Salida::create([
                'fecha' => $request->fecha,
                'sucursal_id' => $request->sucursal_id,
                'tipo' => $request->tipo,
                'motivo' => $request->motivo,
                'observacion' => $request->observacion,
            ]);

            foreach ($request->productos as $item) {
                DetalleSalida::create([
                    'salida_id' => $salida->id,
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                ]);

                // Usar el servicio para restar stock y registrar en Kardex
                InventarioService::salidaNormal(
                    $request->sucursal_id,
                    $item['producto_id'],
                    $item['cantidad'],
                    0, // Precio no relevante en salida
                    'Salida',
                    $salida->id,
                    auth()->id()
                );
            }
        });

        return redirect()->route('salidas.index')->with('success', 'Salida registrada exitosamente.');
    }

    public function index()
    {
        $salidas = Salida::with('detalles.producto', 'sucursal')->latest()->get();
        $idsReversadas = Salida::where('observacion', 'like', 'Reversión de salida #%')
            ->pluck('observacion')
            ->map(fn($obs) => (int) filter_var($obs, FILTER_SANITIZE_NUMBER_INT))
            ->toArray();

        return view('salidas.index', compact('salidas', 'idsReversadas'));
    }

    public function edit($id)
    {
        $salida = Salida::with('detalles')->findOrFail($id);

        if (!\Carbon\Carbon::parse($salida->fecha)->isToday()) {
            return redirect()->route('salidas.index')->with('error', 'Solo puedes editar salidas del mismo día.');
        }

        $productos = Producto::all();
        $sucursales = Sucursal::all();

        return view('salidas.edit', compact('salida', 'productos', 'sucursales'));
    }

    public function update(Request $request, $id)
    {
        $salida = Salida::findOrFail($id);

        if (!\Carbon\Carbon::parse($salida->fecha)->isToday()) {
            return redirect()->route('salidas.index')->with('error', 'Solo se pueden editar salidas del mismo día.');
        }

        $request->validate([
            'fecha' => 'required|date',
            'sucursal_id' => 'required|exists:sucursales,id',
            'tipo' => 'required|string|max:255',
            'motivo' => 'required|string|max:255',
            'observacion' => 'nullable|string|max:255',
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $salida) {
            // Revertir stock anterior
            foreach ($salida->detalles as $detalle) {
                InventarioService::entradaNormal(
                    $salida->sucursal_id,
                    $detalle->producto_id,
                    $detalle->cantidad,
                    0,
                    'Edición Salida',
                    $salida->id,
                    auth()->id()
                );
                $detalle->delete();
            }

            // Actualizar cabecera
            $salida->update([
                'fecha' => $request->fecha,
                'sucursal_id' => $request->sucursal_id,
                'tipo' => $request->tipo,
                'motivo' => $request->motivo,
                'observacion' => $request->observacion,
            ]);

            // Nuevos detalles y descuento de stock
            foreach ($request->productos as $item) {
                DetalleSalida::create([
                    'salida_id' => $salida->id,
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                ]);

                InventarioService::salidaNormal(
                    $request->sucursal_id,
                    $item['producto_id'],
                    $item['cantidad'],
                    0,
                    'Edición Salida',
                    $salida->id,
                    auth()->id()
                );
            }
        });

        return redirect()->route('salidas.index')->with('success', 'Salida actualizada correctamente.');
    }

    public function generarPdf($id)
    {
        $salida = Salida::with(['sucursal', 'detalles.producto'])->findOrFail($id);
        $pdf = Pdf::loadView('salidas.pdf', compact('salida'))->setPaper('A4', 'portrait');
        return $pdf->stream('Salida_' . $salida->id . '.pdf');
    }

    public function reversar($id)
    {
        $salidaOriginal = Salida::with('detalles.producto')->findOrFail($id);

        $existeReversa = Salida::where('observacion', 'like', 'Reversión de salida #%')
            ->where('observacion', 'like', '%'.$salidaOriginal->id.'%')
            ->exists();
        if ($existeReversa) {
            return redirect()->route('salidas.index')->with('error', 'Esta salida ya fue reversada.');
        }

        $diasTranscurridos = \Carbon\Carbon::parse($salidaOriginal->fecha)->diffInDays(now());
        if ($diasTranscurridos > 7) {
            return redirect()->route('salidas.index')->with('error', 'Solo puedes reversar salidas dentro de los 7 días posteriores a su registro.');
        }

        DB::transaction(function () use ($salidaOriginal) {
            $salidaReversa = Salida::create([
                'fecha' => now()->format('Y-m-d'),
                'sucursal_id' => $salidaOriginal->sucursal_id,
                'tipo' => $salidaOriginal->tipo,
                'motivo' => $salidaOriginal->motivo,
                'observacion' => 'Reversión de salida #' . $salidaOriginal->id,
            ]);

            foreach ($salidaOriginal->detalles as $detalle) {
                DetalleSalida::create([
                    'salida_id' => $salidaReversa->id,
                    'producto_id' => $detalle->producto_id,
                    'cantidad' => $detalle->cantidad
                ]);

                InventarioService::entradaNormal(
                    $salidaOriginal->sucursal_id,
                    $detalle->producto_id,
                    $detalle->cantidad,
                    0,
                    'Reversión Salida',
                    $salidaReversa->id,
                    auth()->id()
                );
            }
        });

        return redirect()->route('salidas.index')->with('success', 'Salida reversada correctamente.');
    }
}
