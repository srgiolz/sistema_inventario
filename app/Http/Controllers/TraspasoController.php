<?php

namespace App\Http\Controllers;

use App\Models\Traspaso;
use App\Models\DetalleTraspaso;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\Inventario;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TraspasoController extends Controller
{
    public function create(Request $request)
    {
        $productoNombre = $request->input('producto');
        $origenNombre = $request->input('origen');
        $destinoNombre = $request->input('destino');
        $cantidadSugerida = $request->input('cantidad');

        $productos = Producto::all();
        $sucursales = Sucursal::all();

        return view('traspasos.create', compact(
            'productos', 'sucursales',
            'productoNombre', 'origenNombre', 'destinoNombre', 'cantidadSugerida'
        ));
    }

    public function store(Request $request)
{
    $request->validate([
        'de_sucursal' => 'required|different:a_sucursal|exists:sucursales,id',
        'a_sucursal' => 'required|exists:sucursales,id',
        'fecha' => 'required|date',
        'observacion' => 'nullable|string|max:255',
        'productos' => 'required|array|min:1',
        'productos.*.id_producto' => 'required|exists:productos,id',
        'productos.*.cantidad' => 'required|integer|min:1',
    ]);
    // 🔹 Verificar productos duplicados
$idsProductos = array_column($request->productos, 'id_producto');
if (count($idsProductos) !== count(array_unique($idsProductos))) {
    return back()
        ->withErrors(['error' => '❌ No puedes agregar el mismo producto más de una vez en el traspaso.'])
        ->withInput();
}

    DB::beginTransaction(); // 🔹 Inicio de transacción

    try {
        $tipo = ($request->de_sucursal == 99) ? 'abastecimiento' : 'sucursal';

        $traspaso = Traspaso::create([
            'de_sucursal' => $request->de_sucursal,
            'a_sucursal' => $request->a_sucursal,
            'fecha' => $request->fecha,
            'observacion' => $request->observacion,
            'tipo' => $tipo,
            'estado' => 'pendiente' 
        ]);

        foreach ($request->productos as $item) {
            $productoId = $item['id_producto'];
            $cantidad = $item['cantidad'];

            $origen = Inventario::where('id_producto', $productoId)
                ->where('id_sucursal', $request->de_sucursal)
                ->first();

            if (!$origen || $origen->cantidad < $cantidad) {
                throw new \Exception("❌ Stock insuficiente para el producto ID $productoId.");
            }

            $origen->cantidad -= $cantidad;
            $origen->save();

            DetalleTraspaso::create([
                'traspaso_id' => $traspaso->id,
                'producto_id' => $productoId,
                'cantidad' => $cantidad
            ]);
        }

        DB::commit(); // 🔹 Confirmar cambios
        return redirect()->route('traspasos.index')->with('success', '✅ Traspaso registrado correctamente.');

    } catch (\Exception $e) {
        DB::rollBack(); // 🔹 Deshacer todo si hay error
        return back()->withErrors(['error' => $e->getMessage()])->withInput();
    }
}

    public function index()
    {
        $traspasos = Traspaso::with(['sucursalOrigen', 'sucursalDestino', 'detalles.producto'])
                            ->latest()->get();

        return view('traspasos.index', compact('traspasos'));
    }

    public function obtenerStock($id_producto, $id_sucursal)
    {
        $stock = Inventario::where('id_producto', $id_producto)
            ->where('id_sucursal', $id_sucursal)
            ->value('cantidad');

        return response()->json(['stock' => $stock ?? 0]);
    }
    public function generarPDF($id)
    {
    $traspaso = Traspaso::with(['sucursalOrigen', 'sucursalDestino', 'detalles.producto'])
                        ->findOrFail($id);

    $pdf = Pdf::loadView('traspasos.pdf', compact('traspaso'));
    return $pdf->stream("traspaso_{$traspaso->id}.pdf");
    }

    public function revisar(Traspaso $traspaso)
    {
    $traspaso->load(['sucursalOrigen', 'sucursalDestino', 'detalles.producto']);
    return view('traspasos.revisar', compact('traspaso'));
    }

public function confirmar(Traspaso $traspaso)
{
    if ($traspaso->estado !== 'pendiente') {
        return back()->with('error', 'Este traspaso ya fue confirmado o rechazado.');
    }

    // 🔁 Sumar productos al inventario de la sucursal destino
    foreach ($traspaso->detalles as $detalle) {
        $inventario = Inventario::firstOrCreate(
            [
                'id_producto' => $detalle->producto_id,
                'id_sucursal' => $traspaso->a_sucursal
            ],
            ['cantidad' => 0]
        );

        $inventario->cantidad += $detalle->cantidad;
        $inventario->save();
    }

    // ✅ Marcar como confirmado
    $traspaso->estado = 'confirmado';
    $traspaso->fecha_confirmacion = now();
    $traspaso->usuario_confirmacion_id = auth()->check() ? auth()->id() : null;
    $traspaso->save();

    return redirect()->route('traspasos.index')->with('success', '✅ Traspaso confirmado y stock actualizado.');
}


public function rechazar(Traspaso $traspaso)
{
    if ($traspaso->estado !== 'pendiente') {
        return back()->with('error', 'Este traspaso ya fue confirmado o rechazado.');
    }

    // 🔁 Reversar el stock al origen
    foreach ($traspaso->detalles as $detalle) {
        $inventario = Inventario::firstOrCreate(
            [
                'id_producto' => $detalle->producto_id,
                'id_sucursal' => $traspaso->de_sucursal
            ],
            ['cantidad' => 0]
        );

        $inventario->cantidad += $detalle->cantidad;
        $inventario->save();
    }

    // ❌ Marcar como rechazado
    $traspaso->estado = 'rechazado';
    $traspaso->fecha_confirmacion = now();
    $traspaso->usuario_confirmacion_id = auth()->check() ? auth()->id() : null;
    $traspaso->save();

    return redirect()->route('traspasos.index')->with('error', '❌ Traspaso rechazado y stock devuelto al origen.');
}

public function edit($id)
{
    $traspaso = Traspaso::with(['sucursalOrigen', 'sucursalDestino', 'detalles.producto'])->findOrFail($id);

    if ($traspaso->estado !== 'pendiente') {
        return redirect()->route('traspasos.show', $id)->with('error', 'Este traspaso ya no puede ser editado.');
    }

    return view('traspasos.edit', compact('traspaso'));
}


public function update(Request $request, $id)
{
    $traspaso = Traspaso::with('detalles')->findOrFail($id);

    if ($traspaso->estado !== 'pendiente') {
        return redirect()->route('traspasos.show', $id)->with('error', 'Este traspaso ya fue confirmado o rechazado.');
    }

    DB::beginTransaction();

    try {
        // 1. Revertir el stock anterior (sumar lo que se había descontado)
        foreach ($traspaso->detalles as $detalle) {
            $inventario = Inventario::where('id_producto', $detalle->producto_id)
                ->where('id_sucursal', $traspaso->de_sucursal)
                ->first();

            if ($inventario) {
                $inventario->cantidad += $detalle->cantidad;
                $inventario->save();
            }
        }

        // 2. Eliminar los detalles anteriores
        $traspaso->detalles()->delete();

        // 3. Guardar nuevos productos con sus cantidades
        $productos = $request->productos;
        $cantidades = $request->cantidades;

        foreach ($productos as $index => $producto_id) {
            $cantidad = $cantidades[$index];

            $inventario = Inventario::where('id_producto', $producto_id)
                ->where('id_sucursal', $traspaso->de_sucursal)
                ->first();

            if (!$inventario || $inventario->cantidad < $cantidad) {
                DB::rollBack();
                return back()->with('error', "❌ Stock insuficiente para el producto ID {$producto_id}.");
            }

            // Descontar stock
            $inventario->cantidad -= $cantidad;
            $inventario->save();

            // Crear nuevo detalle
            DetalleTraspaso::create([
                'traspaso_id' => $traspaso->id,
                'producto_id' => $producto_id,
                'cantidad' => $cantidad,
            ]);
        }

        // 4. Actualizar observación si fue modificada
        $traspaso->observacion = $request->observacion;
        $traspaso->save();

        DB::commit();
        return redirect()->route('traspasos.show', $traspaso->id)->with('success', '✅ Traspaso editado correctamente.');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
    }
}
public function show($id)
{
    $traspaso = Traspaso::with(['sucursalOrigen', 'sucursalDestino', 'detalles.producto'])->findOrFail($id);
    return view('traspasos.show', compact('traspaso'));
}
public function productosPorSucursal($idSucursal, Request $request)
{
    $term = strtolower($request->get('term', ''));

    $productos = \DB::table('productos')
        ->join('inventarios', 'productos.id', '=', 'inventarios.id_producto')
        ->where('inventarios.id_sucursal', $idSucursal)
        ->when($term, function ($query, $term) {
            $query->where(function ($sub) use ($term) {
                $sub->whereRaw('LOWER(productos.descripcion) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(productos.item_codigo) LIKE ?', ["%{$term}%"]);
            });
        })
        ->select(
            'productos.id',
            \DB::raw("CONCAT(productos.item_codigo, ' - ', productos.descripcion) as text")
        )
        ->limit(20)
        ->get();

    return response()->json($productos);
}

}
