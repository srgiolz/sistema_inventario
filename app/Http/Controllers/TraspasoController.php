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
            // El formulario puede enviar de_sucursal / a_sucursal, aquí validamos esos IDs
            'de_sucursal' => 'required|different:a_sucursal|exists:sucursales,id',
            'a_sucursal'  => 'required|exists:sucursales,id',
            'fecha'       => 'required|date',
            'observacion' => 'nullable|string|max:255',
            'productos'   => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.cantidad'    => 'required|integer|min:1',
        ]);

        // Verificar productos duplicados
        $idsProductos = array_column($request->productos, 'producto_id');
        if (count($idsProductos) !== count(array_unique($idsProductos))) {
            return back()
                ->withErrors(['error' => '❌ No puedes agregar el mismo producto más de una vez en el traspaso.'])
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $tipo = ($request->de_sucursal == 99) ? 'abastecimiento' : 'sucursal';

            // Mapear a los nombres reales de columnas en la BD
            $traspaso = Traspaso::create([
                'sucursal_origen_id'  => $request->de_sucursal,
                'sucursal_destino_id' => $request->a_sucursal,
                'fecha'       => $request->fecha,
                'observacion' => $request->observacion,
                'tipo'        => $tipo,
                'estado'      => 'pendiente'
            ]);

            foreach ($request->productos as $item) {
                $productoId = $item['producto_id'];
                $cantidad   = $item['cantidad'];

                $origen = Inventario::where('producto_id', $productoId)
                    ->where('sucursal_id', $request->de_sucursal)
                    ->first();

                if (!$origen || $origen->cantidad < $cantidad) {
                    throw new \Exception("❌ Stock insuficiente para el producto ID $productoId.");
                }

                // Descontar del origen
                $origen->cantidad -= $cantidad;
                $origen->save();

                // Crear detalle
                DetalleTraspaso::create([
                    'traspaso_id' => $traspaso->id,
                    'producto_id' => $productoId,
                    'cantidad'    => $cantidad
                ]);
            }

            DB::commit();
            return redirect()->route('traspasos.index')->with('success', '✅ Traspaso registrado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function index()
    {
        $traspasos = Traspaso::with(['sucursalOrigen', 'sucursalDestino', 'detalles.producto'])
            ->latest()->get();

        return view('traspasos.index', compact('traspasos'));
    }

    public function obtenerStock($producto_id, $sucursal_id)
    {
        $stock = Inventario::where('producto_id', $producto_id)
            ->where('sucursal_id', $sucursal_id)
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

        // Sumar al inventario de la sucursal destino
        foreach ($traspaso->detalles as $detalle) {
            $inventario = Inventario::firstOrCreate(
                [
                    'producto_id' => $detalle->producto_id,
                    'sucursal_id' => $traspaso->sucursal_destino_id
                ],
                ['cantidad' => 0]
            );

            $inventario->cantidad += $detalle->cantidad;
            $inventario->save();
        }

        // Marcar como confirmado (columna real: usuario_confirma_id)
        $traspaso->estado = 'confirmado';
        $traspaso->fecha_confirmacion = now();
        $traspaso->usuario_confirma_id = auth()->check() ? auth()->id() : null;
        $traspaso->save();

        return redirect()->route('traspasos.index')->with('success', '✅ Traspaso confirmado y stock actualizado.');
    }

    public function rechazar(Traspaso $traspaso)
    {
        if ($traspaso->estado !== 'pendiente') {
            return back()->with('error', 'Este traspaso ya fue confirmado o rechazado.');
        }

        // Devolver stock al origen
        foreach ($traspaso->detalles as $detalle) {
            $inventario = Inventario::firstOrCreate(
                [
                    'producto_id' => $detalle->producto_id,
                    'sucursal_id' => $traspaso->sucursal_origen_id
                ],
                ['cantidad' => 0]
            );

            $inventario->cantidad += $detalle->cantidad;
            $inventario->save();
        }

        $traspaso->estado = 'rechazado';
        $traspaso->fecha_confirmacion = now();
        $traspaso->usuario_confirma_id = auth()->check() ? auth()->id() : null;
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
            // 1) Revertir stock anterior en el origen
            foreach ($traspaso->detalles as $detalle) {
                $inventario = Inventario::where('producto_id', $detalle->producto_id)
                    ->where('sucursal_id', $traspaso->sucursal_origen_id)
                    ->first();

                if ($inventario) {
                    $inventario->cantidad += $detalle->cantidad;
                    $inventario->save();
                }
            }

            // 2) Eliminar detalles anteriores
            $traspaso->detalles()->delete();

            // 3) Guardar nuevos productos/cantidades (estructura: arrays paralelos)
            $productos  = $request->productos;   // array de IDs
            $cantidades = $request->cantidades;  // array de cantidades

            foreach ($productos as $index => $producto_id) {
                $cantidad = $cantidades[$index];

                $inventario = Inventario::where('producto_id', $producto_id)
                    ->where('sucursal_id', $traspaso->sucursal_origen_id)
                    ->first();

                if (!$inventario || $inventario->cantidad < $cantidad) {
                    DB::rollBack();
                    return back()->with('error', "❌ Stock insuficiente para el producto ID {$producto_id}.");
                }

                // Descontar del origen
                $inventario->cantidad -= $cantidad;
                $inventario->save();

                // Crear detalle
                DetalleTraspaso::create([
                    'traspaso_id' => $traspaso->id,
                    'producto_id' => $producto_id,
                    'cantidad'    => $cantidad,
                ]);
            }

            // 4) Actualizar observación si cambió
            if ($request->filled('observacion')) {
                $traspaso->observacion = $request->observacion;
                $traspaso->save();
            }

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
            ->join('inventarios', 'productos.id', '=', 'inventarios.producto_id')
            ->where('inventarios.sucursal_id', $idSucursal)
            ->when($term, function ($query, $term) {
                $query->where(function ($sub) use ($term) {
                    $sub->whereRaw('LOWER(productos.descripcion) LIKE ?', ["%{$term}%"])
                        ->orWhereRaw('LOWER(productos.codigo_item) LIKE ?', ["%{$term}%"]);
                });
            })
            ->select(
                'productos.id',
                \DB::raw("CONCAT(productos.codigo_item, ' - ', productos.descripcion) as text")
            )
            ->limit(20)
            ->get();

        return response()->json($productos);
    }
}
