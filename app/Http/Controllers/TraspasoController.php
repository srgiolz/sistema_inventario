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
// 📋 Listado principal
public function index(Request $request)
{
    $query = Traspaso::with(['sucursalOrigen', 'sucursalDestino', 'detalles.producto'])
        ->latest();

    // ✅ Filtrar solo si estado viene con valor (no vacío)
    if ($request->filled('estado')) {
        $query->where('estado', $request->estado);
    }

    $traspasos = $query->get();

    // Contador de pendientes (para el badge en el select)
    $pendientesCount = Traspaso::where('estado', 'pendiente')->count();

    return view('traspasos.index', compact('traspasos', 'pendientesCount'));
}
    // 🆕 Formulario de creación
    public function create()
    {
        $productos  = Producto::all();
        $sucursales = Sucursal::all();

        return view('traspasos.create', compact('productos', 'sucursales'));
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
        ]);

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

        return redirect()->route('traspasos.index')->with('success', '✅ Traspaso registrado (pendiente de envío).');
    }   

    // ✏️ Editar
    public function edit($id)
    {
        $traspaso = Traspaso::with('detalles.producto')->findOrFail($id);

        if ($traspaso->estado !== 'pendiente') {
            return redirect()->route('traspasos.index')->with('error', 'Solo se pueden editar traspasos en estado pendiente.');
        }

        $sucursales = Sucursal::all();

        return view('traspasos.edit', compact('traspaso', 'sucursales'));
    }

    // 🔄 Actualizar
    public function update(Request $request, $id)
    {
        $traspaso = Traspaso::with('detalles')->findOrFail($id);

        if ($traspaso->estado !== 'pendiente') {
            return redirect()->route('traspasos.index')->with('error', 'Solo se pueden editar traspasos en estado pendiente.');
        }

        $request->validate([
            'a_sucursal'  => 'required|exists:sucursales,id|different:de_sucursal',
            'productos'   => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.cantidad'    => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $traspaso) {
            $traspaso->update([
                'sucursal_destino_id' => $request->a_sucursal,
                'observacion'         => $request->observacion,
            ]);

            $traspaso->detalles()->delete();
            foreach ($request->productos as $item) {
                $traspaso->detalles()->create([
                    'producto_id' => $item['producto_id'],
                    'cantidad'    => $item['cantidad'],
                ]);
            }
        });

        return redirect()->route('traspasos.index')->with('success', '✅ El traspaso fue actualizado.');
    }

    // 🗑️ No se permite eliminar
    public function destroy($id)
    {
        return redirect()->route('traspasos.index')->with('error', '❌ La eliminación directa no está permitida.');
    }

    // ✅ Confirmar en ORIGEN
    public function confirmarOrigen(Traspaso $traspaso)
    {
        if ($traspaso->estado !== 'pendiente') {
            return back()->with('error', '⚠️ Solo se pueden confirmar traspasos pendientes en el origen.');
        }

        DB::transaction(function () use ($traspaso) {
            foreach ($traspaso->detalles as $detalle) {
                $ok = InventarioService::salidaNormal(
                    $traspaso->sucursal_origen_id,
                    $detalle->producto_id,
                    $detalle->cantidad,
                    0,
                    'TRASPASO_OUT',
                    $traspaso->id,
                    auth()->id()
                );
                if (!$ok) {
                    throw new \Exception("Stock insuficiente en origen para el producto ID {$detalle->producto_id}");
                }
            }

            $traspaso->update([
                'estado' => 'confirmado_origen',
                'fecha_confirmacion' => now(),
                'usuario_confirma_id' => auth()->id(),
            ]);
        });

        return back()->with('success', '✅ Traspaso confirmado en ORIGEN.');
    }

    // ✅ Confirmar en DESTINO
    public function confirmarDestino(Traspaso $traspaso)
    {
        if ($traspaso->estado !== 'confirmado_origen') {
            return back()->with('error', '⚠️ Solo se pueden confirmar en destino los traspasos enviados desde origen.');
        }

        DB::transaction(function () use ($traspaso) {
            foreach ($traspaso->detalles as $detalle) {
                InventarioService::entradaNormal(
                    $traspaso->sucursal_destino_id,
                    $detalle->producto_id,
                    $detalle->cantidad,
                    0,
                    'TRASPASO_IN',
                    $traspaso->id,
                    auth()->id()
                );
            }

            $traspaso->update([
                'estado' => 'confirmado_destino',
                'fecha_recepcion' => now(),
                'usuario_recepciona_id' => auth()->id(),
            ]);
        });

        return back()->with('success', '✅ Traspaso confirmado en DESTINO.');
    }

    // ❌ Rechazar
    public function rechazar(Request $request, Traspaso $traspaso)
    {
        if ($traspaso->estado === 'pendiente') {
            // Cancelación antes de enviar
            $traspaso->update([
                'estado' => 'rechazado',
                'motivo_anulacion' => $request->motivo ?? 'Cancelado en origen',
            ]);
        } elseif ($traspaso->estado === 'confirmado_origen') {
            // Rechazo en destino → revertir salida en origen
            DB::transaction(function () use ($traspaso, $request) {
                foreach ($traspaso->detalles as $detalle) {
                    InventarioService::anulacion(
                        $traspaso->sucursal_origen_id,
                        $detalle->producto_id,
                        $detalle->cantidad,
                        0,
                        'ANULACION_TRASPASO_OUT',
                        $traspaso->id,
                        auth()->id()
                    );
                }

                $traspaso->update([
                    'estado' => 'rechazado',
                    'motivo_anulacion' => $request->motivo ?? 'Rechazado en destino',
                ]);
            });
        } else {
            return back()->with('error', '⚠️ No se puede rechazar este traspaso.');
        }

        return back()->with('success', '🚫 Traspaso rechazado correctamente.');
    }

    // ❌ Anular (opcional, admin)
    public function anular(Request $request, Traspaso $traspaso)
    {
        if ($traspaso->estado !== 'confirmado_destino') {
            return back()->with('error', '⚠️ Solo se pueden anular traspasos ya recibidos.');
        }

        DB::transaction(function () use ($traspaso, $request) {
            foreach ($traspaso->detalles as $detalle) {
                InventarioService::anulacion(
                    $traspaso->sucursal_origen_id,
                    $detalle->producto_id,
                    $detalle->cantidad,
                    0,
                    'ANULACION_TRASPASO_OUT',
                    $traspaso->id,
                    auth()->id()
                );
                InventarioService::anulacion(
                    $traspaso->sucursal_destino_id,
                    $detalle->producto_id,
                    $detalle->cantidad,
                    0,
                    'ANULACION_TRASPASO_IN',
                    $traspaso->id,
                    auth()->id()
                );
            }

            $traspaso->update([
                'estado' => 'anulado',
                'motivo_anulacion' => $request->motivo ?? 'Anulado manualmente',
            ]);
        });

        return back()->with('success', '❌ Traspaso anulado correctamente.');
    }

    // 📄 Generar PDF
    public function generarPDF($id)
    {
        $traspaso = Traspaso::with(['sucursalOrigen', 'sucursalDestino', 'detalles.producto'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('traspasos.pdf', compact('traspaso'));
        return $pdf->stream("traspaso_{$traspaso->id}.pdf");
    }
    // 📦 API: productos disponibles por sucursal
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
// 📦 API: obtener stock disponible de un producto en una sucursal
public function obtenerStock($idProducto, $idSucursal)
    {
        $stock = DB::table('inventarios')
            ->where('producto_id', $idProducto)
            ->where('sucursal_id', $idSucursal)
            ->value('cantidad');

        return response()->json(['stock' => $stock ?? 0]);
    }
        // 🔍 Revisar
    public function revisar($id)
    {
        $traspaso = Traspaso::with(['sucursalOrigen', 'sucursalDestino', 'detalles.producto'])
            ->findOrFail($id);

        return view('traspasos.revisar', compact('traspaso'));
    }
}

