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
    // 📋 Listado principal
    public function index()
    {
        $traspasos = Traspaso::with(['sucursalOrigen', 'sucursalDestino', 'detalles.producto'])
            ->latest()
            ->get();

        $pendientesCount = Traspaso::where('estado', 'pendiente')->count();

        return view('traspasos.index', compact('traspasos', 'pendientesCount'));
    }

    // 🆕 Formulario de creación
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

    // 💾 Guardar traspaso
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

        // 🚫 Evitar productos duplicados
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

    // 👀 Mostrar un traspaso simple
    public function show($id)
    {
        $traspaso = Traspaso::with(['sucursalOrigen', 'sucursalDestino', 'detalles.producto'])->findOrFail($id);
        return view('traspasos.show', compact('traspaso'));
    }

    // ✏️ Editar
    public function edit($id)
    {
        $traspaso = Traspaso::with('detalles.producto')->findOrFail($id);
        $sucursales = Sucursal::all();

        $stocks = DB::table('inventarios')
            ->where('sucursal_id', $traspaso->sucursal_origen_id)
            ->pluck('cantidad', 'producto_id');

        return view('traspasos.edit', compact('traspaso', 'sucursales', 'stocks'));
    }

    // 🔄 Actualizar
    public function update(Request $request, $id)
    {
        $traspaso = Traspaso::with('detalles')->findOrFail($id);

        if ($traspaso->estado !== 'pendiente') {
            return redirect()->route('traspasos.index')
                ->with('error', 'Solo se pueden editar traspasos en estado pendiente.');
        }

        $request->validate([
            'a_sucursal'  => 'required|exists:sucursales,id|different:de_sucursal',
            'productos'   => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.cantidad'    => 'required|integer|min:1',
        ]);

        $idsProductos = array_column($request->productos, 'producto_id');
        if (count($idsProductos) !== count(array_unique($idsProductos))) {
            return back()->withErrors(['error' => '❌ No puedes repetir el mismo producto.'])->withInput();
        }

        DB::transaction(function () use ($request, $traspaso) {
            $origenId = $traspaso->detalles()->count() > 0
                ? $traspaso->sucursal_origen_id
                : $request->de_sucursal;

            $traspaso->update([
                'sucursal_origen_id'  => $origenId,
                'sucursal_destino_id' => $request->a_sucursal,
                'observacion'         => $request->observacion,
            ]);

            $detallesExistentes = $traspaso->detalles->keyBy('producto_id');
            $productosEnviados = collect($request->productos)->keyBy('producto_id');

            foreach ($productosEnviados as $prodId => $data) {
                if ($detallesExistentes->has($prodId)) {
                    $detallesExistentes[$prodId]->update(['cantidad' => $data['cantidad']]);
                } else {
                    $traspaso->detalles()->create([
                        'producto_id' => $prodId,
                        'cantidad'    => $data['cantidad'],
                    ]);
                }
            }

            $idsEnviados = $productosEnviados->keys();
            foreach ($detallesExistentes as $prodId => $detalle) {
                if (!$idsEnviados->contains($prodId)) {
                    $detalle->delete();
                }
            }
        });

        return redirect()->route('traspasos.index')->with('success', '✅ El traspaso fue actualizado correctamente.');
    }

    // 🗑️ (opcional) Eliminar — si no lo usas, lo dejamos vacío
    public function destroy($id)
    {
        return redirect()->route('traspasos.index')->with('error', '❌ La eliminación directa no está permitida.');
    }

    // 📌 Pendientes
    public function pendientes()
    {
        $traspasos = Traspaso::with(['sucursalOrigen', 'sucursalDestino', 'detalles.producto'])
            ->where('estado', 'pendiente')
            ->latest()
            ->get();

        return view('traspasos.pendientes', compact('traspasos'));
    }

    // 🔍 Revisar
    public function revisar($id)
    {
        $traspaso = Traspaso::with(['sucursalOrigen', 'sucursalDestino', 'detalles.producto'])
            ->findOrFail($id);

        return view('traspasos.revisar', compact('traspaso'));
    }

    // 📦 Productos disponibles por sucursal (API Select2)
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

    // 📊 Stock individual (para Ajax)
    public function obtenerStock($idProducto, $idSucursal)
    {
        $stock = DB::table('inventarios')
            ->where('producto_id', $idProducto)
            ->where('sucursal_id', $idSucursal)
            ->value('cantidad');

        return response()->json(['stock' => $stock ?? 0]);
    }

    // ✅ Confirmar en ORIGEN
    public function confirmarOrigen(Traspaso $traspaso) { /* ...como ya lo tienes... */ }

    // ✅ Confirmar en DESTINO
    public function confirmarDestino(Traspaso $traspaso) { /* ...como ya lo tienes... */ }

    // ❌ Anular
    public function anular(Request $request, Traspaso $traspaso) { /* ...como ya lo tienes... */ }

    // ❌ Rechazar
    public function rechazar(Request $request, Traspaso $traspaso) { /* ...como ya lo tienes... */ }

    // 📄 Generar PDF
    public function generarPDF($id)
    {
        $traspaso = Traspaso::with(['sucursalOrigen', 'sucursalDestino', 'detalles.producto'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('traspasos.pdf', compact('traspaso'));
        return $pdf->stream("traspaso_{$traspaso->id}.pdf");
    }
}

