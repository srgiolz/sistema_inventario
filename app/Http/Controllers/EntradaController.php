<?php

namespace App\Http\Controllers;

use App\Models\Entrada;
use App\Models\DetalleEntrada;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class EntradaController extends Controller
{
  public function index()
{
    $entradas = Entrada::with(['sucursal', 'detalles.producto'])->latest()->get();

    // Obtener IDs de entradas que ya fueron reversadas
    $idsReversadas = Entrada::where('observacion', 'like', 'Reversión de entrada #%')
        ->pluck('observacion')
        ->map(function ($obs) {
            return (int) filter_var($obs, FILTER_SANITIZE_NUMBER_INT);
        })
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
            'id_sucursal' => 'required|exists:sucursales,id',
            'fecha' => 'required|date',
            'tipo' => 'required|string|max:100',
            'observacion' => 'nullable|string|max:255',
            'productos.*.id_producto' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio_unitario' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            // Crear entrada general
            $entrada = Entrada::create([
                'id_sucursal' => $request->id_sucursal,
                'fecha' => $request->fecha,
                'tipo' => $request->tipo,
                'observacion' => $request->observacion,
            ]);

            // Registrar productos asociados
            foreach ($request->productos as $producto) {
                DetalleEntrada::create([
                    'entrada_id' => $entrada->id,
                    'id_producto' => $producto['id_producto'],
                    'cantidad' => $producto['cantidad'],
                    'precio_unitario' => $producto['precio_unitario'] ?? null,
                ]);

                // Actualizar inventario
                $inventario = Inventario::firstOrCreate(
                    [
                        'id_producto' => $producto['id_producto'],
                        'id_sucursal' => $request->id_sucursal,
                    ],
                    ['cantidad' => 0]
                );

                $inventario->cantidad += $producto['cantidad'];
                $inventario->save();
            }
        });

        return redirect()->route('entradas.index')->with('success', 'Entrada registrada correctamente.');
    }
    public function reversar($id)
{
    // Obtener la entrada con sus detalles y productos relacionados
    $entradaOriginal = Entrada::with('detalles.producto')->findOrFail($id);

    // VALIDACIÓN GLOBAL POR PRODUCTO
    foreach ($entradaOriginal->detalles as $detalle) {
        // 1. Verificar si existe otra entrada posterior del mismo producto
        $hayOtraEntradaPosterior = Entrada::where('id_sucursal', $entradaOriginal->id_sucursal)
            ->where('fecha', '>', $entradaOriginal->fecha)
            ->whereHas('detalles', function ($q) use ($detalle) {
                $q->where('id_producto', $detalle->id_producto);
            })
            ->exists();

        if ($hayOtraEntradaPosterior) {
            return redirect()->route('entradas.index')->with('error', 'No se puede reversar esta entrada porque el producto "' . $detalle->producto->descripcion . '" tiene otra entrada posterior.');
        }

        // 2. Verificar si el stock actual alcanza para reversar
        $inventario = Inventario::where('id_producto', $detalle->id_producto)
            ->where('id_sucursal', $entradaOriginal->id_sucursal)
            ->first();

        if (!$inventario || $inventario->cantidad < $detalle->cantidad) {
            return redirect()->route('entradas.index')->with('error', 'No se puede reversar esta entrada porque el stock actual del producto "' . $detalle->producto->descripcion . '" no es suficiente.');
        }
    }

    // 🛠 Proceso de reversa si pasa la validación
    DB::transaction(function () use ($entradaOriginal) {
        $entradaReversa = Entrada::create([
            'id_sucursal' => $entradaOriginal->id_sucursal,
            'fecha' => now()->format('Y-m-d'),
            'tipo' => $entradaOriginal->tipo,
            'observacion' => 'Reversión de entrada #' . $entradaOriginal->id,
        ]);

        foreach ($entradaOriginal->detalles as $detalle) {
            DetalleEntrada::create([
                'entrada_id' => $entradaReversa->id,
                'id_producto' => $detalle->id_producto,
                'cantidad' => -1 * $detalle->cantidad,
                'precio_unitario' => $detalle->precio_unitario,
            ]);

            $inventario = Inventario::where('id_producto', $detalle->id_producto)
                ->where('id_sucursal', $entradaOriginal->id_sucursal)
                ->first();

            if ($inventario) {
                $inventario->cantidad -= $detalle->cantidad;
                $inventario->save();
            }
        }
    });

    return redirect()->route('entradas.index')->with('success', 'Entrada reversada correctamente.');
}

public function edit($id)
{
    $entrada = Entrada::with('detalles')->findOrFail($id);

    // Validar que la entrada sea del mismo día
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

    // Validar que solo se permita editar si es del mismo día
    if (!\Carbon\Carbon::parse($entrada->fecha)->isToday()) {
        return redirect()->route('entradas.index')->with('error', 'Solo se pueden editar entradas del mismo día.');
    }

    // Validaciones de los campos
    $request->validate([
        'fecha' => 'required|date',
        'tipo' => 'required|string|max:100',
        'observacion' => 'nullable|string|max:255',
        'productos' => 'required|array|min:1',
        'productos.*.id_producto' => 'required|exists:productos,id',
        'productos.*.cantidad' => 'required|numeric|min:1',
        'productos.*.precio_unitario' => 'nullable|numeric|min:0',
    ]);

    // Validar productos duplicados
    $productosIds = collect($request->productos)->pluck('id_producto');
    if ($productosIds->duplicates()->isNotEmpty()) {
        return back()->withErrors(['productos' => 'No se permiten productos duplicados.'])->withInput();
    }

    DB::transaction(function () use ($request, $entrada) {
        // Actualizar cabecera
        $entrada->update([
            'fecha' => $request->fecha,
            'tipo' => $request->tipo,
            'observacion' => $request->observacion,
        ]);

        // Revertir stock de los detalles anteriores (si manejas stock dinámico)
        foreach ($entrada->detalles as $detalle) {
            $inventario = Inventario::where('id_producto', $detalle->id_producto)
                ->where('id_sucursal', $entrada->id_sucursal)
                ->first();

            if ($inventario) {
                $inventario->cantidad -= $detalle->cantidad;
                $inventario->save();
            }

            $detalle->delete();
        }

        // Registrar los nuevos productos y ajustar inventario
        foreach ($request->productos as $producto) {
            $detalle = $entrada->detalles()->create([
                'id_producto' => $producto['id_producto'],
                'cantidad' => $producto['cantidad'],
                'precio_unitario' => $producto['precio_unitario'] ?? 0,
            ]);

            // Aumentar stock
            $inventario = Inventario::firstOrCreate(
                [
                    'id_producto' => $producto['id_producto'],
                    'id_sucursal' => $entrada->id_sucursal,
                ],
                ['cantidad' => 0]
            );

            $inventario->cantidad += $producto['cantidad'];
            $inventario->save();
        }
    });

    return redirect()->route('entradas.index')->with('success', 'Entrada actualizada correctamente.');
}
public function generarPdf($id)
{
    $entrada = Entrada::with(['sucursal', 'detalles.producto'])->findOrFail($id);

    $pdf = Pdf::loadView('entradas.pdf', compact('entrada'))
        ->setPaper('A4', 'portrait');

    return $pdf->stream('Entrada_'.$entrada->id.'.pdf');
}
}


