<?php

namespace App\Http\Controllers;

use App\Models\Traspaso;
use App\Models\DetalleTraspaso;
use App\Models\Producto;
use App\Models\Sucursal;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Services\InventarioService;

class TraspasoController extends Controller
{
    public function create(Request $request)
    {
        $productoNombre   = $request->input('producto');
        $origenNombre     = $request->input('origen');
        $destinoNombre    = $request->input('destino');
        $cantidadSugerida = $request->input('cantidad');

        $productos  = Producto::all();
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
            'a_sucursal'  => 'required|exists:sucursales,id',
            'fecha'       => 'required|date',
            'observacion' => 'nullable|string|max:255',
            'productos'   => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.cantidad'    => 'required|integer|min:1',
        ], [
            'de_sucursal.different' => 'La sucursal de origen y la de destino deben ser diferentes.',
            'productos.required'    => 'Debes agregar al menos un producto al traspaso.',
            'productos.*.cantidad.min' => 'La cantidad debe ser mayor a 0.',
        ]);

        // Evitar productos duplicados
        $idsProductos = array_column($request->productos, 'producto_id');
        if (count($idsProductos) !== count(array_unique($idsProductos))) {
            return back()
                ->withErrors(['error' => '❌ No puedes agregar el mismo producto más de una vez en el traspaso.'])
                ->withInput();
        }

        DB::transaction(function () use ($request) {
            $tipo = ($request->de_sucursal == 99) ? 'abastecimiento' : 'sucursal';

            $traspaso = Traspaso::create([
                'sucursal_origen_id'  => $request->de_sucursal,
                'sucursal_destino_id' => $request->a_sucursal,
                'fecha'       => $request->fecha,
                'observacion' => $request->observacion,
                'tipo'        => $tipo,
                'estado'      => 'pendiente'
            ]);

            foreach ($request->productos as $item) {
                DetalleTraspaso::create([
                    'traspaso_id' => $traspaso->id,
                    'producto_id' => $item['producto_id'],
                    'cantidad'    => $item['cantidad']
                ]);
            }
        });

        return redirect()->route('traspasos.index')->with('success', '✅ Traspaso registrado correctamente (pendiente de confirmación).');
    }

    public function index()
{
    $traspasos = Traspaso::with(['sucursalOrigen', 'sucursalDestino', 'detalles.producto'])
        ->latest()->get();

    $pendientesCount = Traspaso::where('estado', 'pendiente')->count();

    return view('traspasos.index', compact('traspasos', 'pendientesCount'));
}


    public function productosPorSucursal($idSucursal, Request $request)
    {
        $term = $request->get('term', '');

        $productos = DB::table('inventarios')
            ->join('productos', 'inventarios.producto_id', '=', 'productos.id')
            ->where('inventarios.sucursal_id', $idSucursal)
            ->where('inventarios.cantidad', '>', 0)
            ->when($term, function ($q) use ($term) {
                $q->where('productos.descripcion', 'like', "%{$term}%")
                  ->orWhere('productos.codigo_item', 'like', "%{$term}%");
            })
            ->select(
                'productos.id as id',
                DB::raw("CONCAT(productos.codigo_item, ' - ', productos.descripcion) as text")
            )
            ->get();

        return response()->json($productos);
    }

    public function obtenerStock($idProducto, $idSucursal)
    {
        $stock = DB::table('inventarios')
            ->where('producto_id', $idProducto)
            ->where('sucursal_id', $idSucursal)
            ->value('cantidad');

        return response()->json([
            'stock' => $stock ?? 0
        ]);
    }

    public function revisar($id)
    {
        $traspaso = Traspaso::with(['sucursalOrigen', 'sucursalDestino', 'detalles.producto'])
            ->findOrFail($id);

        return view('traspasos.revisar', compact('traspaso'));
    }
    public function pendientes()
{
    $traspasos = Traspaso::with(['sucursalOrigen', 'sucursalDestino', 'detalles.producto'])
        ->where('estado', 'pendiente')
        ->latest()
        ->get();

    return view('traspasos.pendientes', compact('traspasos'));
}


public function edit($id)
{
    $traspaso = Traspaso::with('detalles.producto')->findOrFail($id);
    $sucursales = Sucursal::all();

    return view('traspasos.edit', compact('traspaso', 'sucursales'));
}


    public function update(Request $request, $id)
{
    $traspaso = Traspaso::with('detalles')->findOrFail($id);

    // 🔒 Solo pendiente se puede editar
    if ($traspaso->estado !== 'pendiente') {
        return redirect()->route('traspasos.index')
            ->with('error', 'Solo se pueden editar traspasos en estado pendiente.');
    }

    // ✅ Validaciones fuertes
    $request->validate([
        'de_sucursal' => 'required|different:a_sucursal|exists:sucursales,id',
        'a_sucursal'  => 'required|exists:sucursales,id',
        'productos'   => 'required|array|min:1',
        'productos.*.producto_id' => 'required|exists:productos,id',
        'productos.*.cantidad'    => 'required|integer|min:1',
    ], [
        'productos.required' => 'Debes agregar al menos un producto.',
        'productos.*.cantidad.min' => 'La cantidad debe ser mayor a 0.',
    ]);

    // 🚫 Evitar duplicados en el request
    $idsProductos = array_column($request->productos, 'producto_id');
    if (count($idsProductos) !== count(array_unique($idsProductos))) {
        return back()->withErrors(['error' => '❌ No puedes repetir el mismo producto.'])->withInput();
    }

    DB::transaction(function () use ($request, $traspaso) {
        // 🔒 Bloquear cambio de ORIGEN si ya había detalles
        if ($traspaso->detalles()->count() > 0) {
            $origenId = $traspaso->sucursal_origen_id;
        } else {
            $origenId = $request->de_sucursal;
        }

        // ✅ Actualizar cabecera (origen, destino, observación)
        $traspaso->update([
            'sucursal_origen_id'  => $origenId,
            'sucursal_destino_id' => $request->a_sucursal,
            'observacion'         => $request->observacion,
        ]);

        // ✅ Manejo de detalles
        $detallesExistentes = $traspaso->detalles->keyBy('producto_id');
        $productosEnviados = collect($request->productos)->keyBy('producto_id');

        // 1. Actualizar cantidades o crear nuevos
        foreach ($productosEnviados as $prodId => $data) {
            if ($detallesExistentes->has($prodId)) {
                $detallesExistentes[$prodId]->update([
                    'cantidad' => $data['cantidad'],
                ]);
            } else {
                $traspaso->detalles()->create([
                    'producto_id' => $prodId,
                    'cantidad'    => $data['cantidad'],
                ]);
            }
        }

        // 2. Eliminar productos que ya no están en el request
        $idsEnviados = $productosEnviados->keys();
        foreach ($detallesExistentes as $prodId => $detalle) {
            if (!$idsEnviados->contains($prodId)) {
                $detalle->delete();
            }
        }
    });

    return redirect()->route('traspasos.index')
        ->with('success', '✅ El traspaso fue actualizado correctamente.');
}


    public function confirmar(Traspaso $traspaso)
    {
        if ($traspaso->estado !== 'pendiente') {
            return back()->with('error', 'Este traspaso ya fue confirmado o rechazado.');
        }

        DB::transaction(function () use ($traspaso) {
            foreach ($traspaso->detalles as $detalle) {
                // 1. Restar stock en origen
                InventarioService::salidaNormal(
                    $traspaso->sucursal_origen_id,
                    $detalle->producto_id,
                    $detalle->cantidad,
                    0,
                    'Traspaso Origen',
                    $traspaso->id,
                    auth()->id()
                );

                // 2. Sumar stock en destino
                InventarioService::entradaNormal(
                    $traspaso->sucursal_destino_id,
                    $detalle->producto_id,
                    $detalle->cantidad,
                    0,
                    'Traspaso Destino',
                    $traspaso->id,
                    auth()->id()
                );
            }

            $traspaso->estado = 'confirmado';
            $traspaso->fecha_confirmacion = now();
            $traspaso->usuario_confirma_id = auth()->id();
            $traspaso->save();
        });

        return redirect()->route('traspasos.index')->with('success', '✅ Traspaso confirmado y stock actualizado.');
    }

    public function rechazar(Traspaso $traspaso)
    {
        if ($traspaso->estado !== 'pendiente') {
            return back()->with('error', 'Este traspaso ya fue confirmado o rechazado.');
        }

        DB::transaction(function () use ($traspaso) {
            $traspaso->estado = 'rechazado';
            $traspaso->fecha_confirmacion = now();
            $traspaso->usuario_confirma_id = auth()->id();
            $traspaso->save();
        });

        return redirect()->route('traspasos.index')->with('error', '❌ Traspaso rechazado (no se movió inventario).');
    }

    public function generarPDF($id)
    {
        $traspaso = Traspaso::with(['sucursalOrigen', 'sucursalDestino', 'detalles.producto'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('traspasos.pdf', compact('traspaso'));
        return $pdf->stream("traspaso_{$traspaso->id}.pdf");
    }

    public function show($id)
    {
        $traspaso = Traspaso::with(['sucursalOrigen', 'sucursalDestino', 'detalles.producto'])->findOrFail($id);
        return view('traspasos.show', compact('traspaso'));
    }
}
