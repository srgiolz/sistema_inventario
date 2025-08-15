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
                // Restar stock del origen usando InventarioService
                InventarioService::salidaNormal(
                    $request->de_sucursal,
                    $item['producto_id'],
                    $item['cantidad'],
                    0,
                    'Traspaso Origen',
                    $traspaso->id,
                    auth()->id()
                );

                DetalleTraspaso::create([
                    'traspaso_id' => $traspaso->id,
                    'producto_id' => $item['producto_id'],
                    'cantidad'    => $item['cantidad']
                ]);
            }
        });

        return redirect()->route('traspasos.index')->with('success', '✅ Traspaso registrado correctamente.');
    }

    public function index()
    {
        $traspasos = Traspaso::with(['sucursalOrigen', 'sucursalDestino', 'detalles.producto'])
            ->latest()->get();

        return view('traspasos.index', compact('traspasos'));
    }

    public function confirmar(Traspaso $traspaso)
    {
        if ($traspaso->estado !== 'pendiente') {
            return back()->with('error', 'Este traspaso ya fue confirmado o rechazado.');
        }

        DB::transaction(function () use ($traspaso) {
            foreach ($traspaso->detalles as $detalle) {
                // Sumar stock en la sucursal destino
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
            foreach ($traspaso->detalles as $detalle) {
                // Devolver stock al origen
                InventarioService::entradaNormal(
                    $traspaso->sucursal_origen_id,
                    $detalle->producto_id,
                    $detalle->cantidad,
                    0,
                    'Rechazo Traspaso',
                    $traspaso->id,
                    auth()->id()
                );
            }

            $traspaso->estado = 'rechazado';
            $traspaso->fecha_confirmacion = now();
            $traspaso->usuario_confirma_id = auth()->id();
            $traspaso->save();
        });

        return redirect()->route('traspasos.index')->with('error', '❌ Traspaso rechazado y stock devuelto al origen.');
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
