<?php

namespace App\Http\Controllers;

use App\Models\Salida;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DetalleSalida;
use Barryvdh\DomPDF\Facade\Pdf;


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

    // IDs de salidas ya reversadas
    $idsReversadas = Salida::where('observacion', 'like', 'Reversión de salida #%')
        ->pluck('observacion')
        ->map(function ($obs) {
            return (int) filter_var($obs, FILTER_SANITIZE_NUMBER_INT);
        })
        ->toArray();

    return view('salidas.index', compact('salidas', 'idsReversadas'));
}
public function edit($id)
{
    $salida = Salida::with('detalles')->findOrFail($id);

    // Validar que la salida sea del mismo día
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

    // Restricción: solo editar si es del mismo día
    if (!\Carbon\Carbon::parse($salida->fecha)->isToday()) {
        return redirect()->route('salidas.index')->with('error', 'Solo se pueden editar salidas del mismo día.');
    }

    // Validaciones
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

    DB::transaction(function () use ($request, $salida) {
        // Revertir stock anterior
        foreach ($salida->detalles as $detalle) {
            $inventario = Inventario::where('id_producto', $detalle->id_producto)
                ->where('id_sucursal', $salida->id_sucursal)
                ->first();
            if ($inventario) {
                $inventario->cantidad += $detalle->cantidad; // devolver stock
                $inventario->save();
            }
            $detalle->delete();
        }

        // Actualizar cabecera
        $salida->update([
            'fecha' => $request->fecha,
            'id_sucursal' => $request->id_sucursal,
            'tipo' => $request->tipo,
            'motivo' => $request->motivo,
            'observacion' => $request->observacion,
        ]);

        // Registrar nuevos detalles y descontar stock
        foreach ($request->productos as $item) {
            $inventario = Inventario::where('id_producto', $item['id_producto'])
                ->where('id_sucursal', $request->id_sucursal)
                ->first();

            if (!$inventario || $inventario->cantidad < $item['cantidad']) {
                throw new \Exception("Stock insuficiente para el producto ID: {$item['id_producto']}");
            }

            DetalleSalida::create([
                'salida_id' => $salida->id,
                'id_producto' => $item['id_producto'],
                'cantidad' => $item['cantidad'],
            ]);

            $inventario->cantidad -= $item['cantidad'];
            $inventario->save();
        }
    });

    return redirect()->route('salidas.index')->with('success', 'Salida actualizada correctamente.');
}

public function generarPdf($id)
{
    $salida = Salida::with(['sucursal', 'detalles.producto'])->findOrFail($id);

    $pdf = Pdf::loadView('salidas.pdf', compact('salida'))
        ->setPaper('A4', 'portrait');

    return $pdf->stream('Salida_'.$salida->id.'.pdf');
}
public function reversar($id)
{
    $salidaOriginal = Salida::with('detalles.producto')->findOrFail($id);

    // 1️⃣ Validar si ya existe reversa
    $existeReversa = Salida::where('observacion', 'like', 'Reversión de salida #%')
        ->where('observacion', 'like', '%'.$salidaOriginal->id.'%')
        ->exists();
    if ($existeReversa) {
        return redirect()->route('salidas.index')->with('error', 'Esta salida ya fue reversada.');
    }

    // 2️⃣ Validar límite de tiempo (7 días)
    $diasTranscurridos = \Carbon\Carbon::parse($salidaOriginal->fecha)->diffInDays(now());
    if ($diasTranscurridos > 7) {
        return redirect()->route('salidas.index')->with('error', 'Solo puedes reversar salidas dentro de los 7 días posteriores a su registro.');
    }

    // 3️⃣ Proceso de reversa
    DB::transaction(function () use ($salidaOriginal) {
        $salidaReversa = Salida::create([
            'fecha' => now()->format('Y-m-d'),
            'id_sucursal' => $salidaOriginal->id_sucursal,
            'tipo' => $salidaOriginal->tipo,
            'motivo' => $salidaOriginal->motivo,
            'observacion' => 'Reversión de salida #' . $salidaOriginal->id,
        ]);

        foreach ($salidaOriginal->detalles as $detalle) {
            // Guardar detalle en reversa (cantidad positiva)
            DetalleSalida::create([
                'salida_id' => $salidaReversa->id,
                'id_producto' => $detalle->id_producto,
                'cantidad' => $detalle->cantidad
            ]);

            // Sumar al inventario
            $inventario = Inventario::firstOrCreate(
                [
                    'id_producto' => $detalle->id_producto,
                    'id_sucursal' => $salidaOriginal->id_sucursal
                ],
                ['cantidad' => 0]
            );

            $inventario->cantidad += $detalle->cantidad;
            $inventario->save();
        }
    });

    return redirect()->route('salidas.index')->with('success', 'Salida reversada correctamente.');
}
}
