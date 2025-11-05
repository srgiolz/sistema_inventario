<?php

namespace App\Http\Controllers;

use App\Models\Salida;
use App\Models\Producto;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DetalleSalida;
use App\Services\InventarioService;
use Barryvdh\DomPDF\Facade\Pdf;

class SalidaController extends Controller
{
    public function index()
    {
        $salidas = Salida::with('detalles.producto', 'sucursal')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('salidas.index', compact('salidas'));
    }

    public function create()
    {
        $productos = Producto::where('activo', 1)->get();
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
                'estado' => 'pendiente',
            ]);

            foreach ($request->productos as $item) {
                DetalleSalida::create([
                    'salida_id' => $salida->id,
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                ]);
            }
        });

        return redirect()->route('salidas.index')->with('success', 'Salida registrada en estado pendiente.');
    }

    public function edit($id)
    {
        $salida = Salida::with('detalles')->findOrFail($id);

        if ($salida->estado !== 'pendiente') {
            return redirect()->route('salidas.index')->with('error', 'Solo se pueden editar salidas pendientes.');
        }

        $productos = Producto::where('activo', 1)->get();
        $sucursales = Sucursal::all();

        return view('salidas.edit', compact('salida', 'productos', 'sucursales'));
    }

    public function update(Request $request, $id)
    {
        $salida = Salida::with('detalles')->findOrFail($id);

        if ($salida->estado !== 'pendiente') {
            return redirect()->route('salidas.index')->with('error', 'No se puede editar una salida confirmada o anulada.');
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
            $salida->update([
                'fecha' => $request->fecha,
                'sucursal_id' => $request->sucursal_id,
                'tipo' => $request->tipo,
                'motivo' => $request->motivo,
                'observacion' => $request->observacion,
            ]);

            $salida->detalles()->delete();

            foreach ($request->productos as $item) {
                DetalleSalida::create([
                    'salida_id' => $salida->id,
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                ]);
            }
        });

        return redirect()->route('salidas.index')->with('success', 'Salida actualizada correctamente.');
    }
    
public function confirm($id)
{
    try {
        DB::transaction(function () use ($id) {
            $salida = Salida::with('detalles.producto')->findOrFail($id);

            if ($salida->estado !== 'pendiente') {
                throw new \Exception('Solo se pueden confirmar salidas pendientes.');
            }

            foreach ($salida->detalles as $detalle) {
                $ok = InventarioService::salidaNormal(
                    $salida->sucursal_id,
                    $detalle->producto_id,
                    $detalle->cantidad,
                    0,
                    'SALIDA',
                    $salida->id,
                    auth()->id()
                );

                // 🚨 Si no hay stock → detener la confirmación
                if ($ok === false) {
                    throw new \Exception("Stock insuficiente para el producto: {$detalle->producto->descripcion}");
                }
            }

            // 🚀 Solo llega aquí si todos los productos tienen stock
            $salida->update([
                'estado' => 'confirmado',
                'fecha_confirmacion' => now(),
                'usuario_confirma_id' => auth()->id(),
            ]);
        });

        return redirect()->route('salidas.index')->with('success', 'Salida confirmada y stock actualizado.');
    } catch (\Exception $e) {
        return redirect()->route('salidas.index')->with('error', $e->getMessage());
    }
}


public function anular(Request $request, $id)
{
    DB::beginTransaction();
    try {
        $salida = Salida::with('detalles')->findOrFail($id);

        if ($salida->estado === 'pendiente') {
            // ❌ Caso 1: Salida en pendiente → nunca afectó inventario
            $salida->update([
                'estado' => 'anulado',
                'motivo_anulacion' => $request->motivo_anulacion ?? 'Anulación de salida pendiente',
            ]);

        } elseif ($salida->estado === 'confirmado') {
            // ✅ Caso 2: Salida confirmada → devolver stock
            foreach ($salida->detalles as $detalle) {
                InventarioService::entradaNormal(
                    $salida->sucursal_id,
                    $detalle->producto_id,
                    $detalle->cantidad,
                    0,
                    'ANULACION_SALIDA',
                    $salida->id,
                    auth()->id()
                );
            }

            $salida->update([
                'estado' => 'anulado',
                'motivo_anulacion' => $request->motivo_anulacion ?? 'Anulación de salida confirmada',
            ]);
        } else {
            return redirect()->route('salidas.index')
                ->with('error', 'Solo se pueden anular salidas pendientes o confirmadas.');
        }

        DB::commit();
        return redirect()->route('salidas.index')->with('success', 'Salida anulada correctamente.');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->route('salidas.index')->with('error', 'Error al anular salida: ' . $e->getMessage());
    }
}
public function revisar($id)
{
    $salida = \App\Models\Salida::with(['sucursal', 'detalles.producto'])->findOrFail($id);
    return view('salidas.revisar', compact('salida'));
}


    public function generarPdf($id)
    {
        $salida = Salida::with(['sucursal', 'detalles.producto'])->findOrFail($id);
        $pdf = Pdf::loadView('salidas.pdf', compact('salida'))->setPaper('A4', 'portrait');
        return $pdf->stream('Salida_' . $salida->id . '.pdf');
    }
}
