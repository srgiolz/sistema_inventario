<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Sucursal;
use App\Models\TipoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Services\InventarioService;

class VentaController extends Controller
{
    public function create()
    {
        return view('ventas.create', [
            'clientes'   => Cliente::all(),
            'productos'  => Producto::with('inventarios')->get(),
            'sucursales' => Sucursal::all(),
            'tiposPago'  => TipoPago::where('activo', 1)->get(), // ahora dinámico
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id'                 => 'required|exists:clientes,id',
            'sucursal_id'                => 'required|exists:sucursales,id',
            'productos'                  => 'required|array|min:1',
            'productos.*.producto_id'    => 'required|exists:productos,id',
            'productos.*.cantidad'       => 'required|numeric|min:1',
            'productos.*.precio'         => 'required|numeric|min:0',
            'productos.*.descuento'      => 'nullable|numeric|min:0',
            'tipo_pago_id'               => 'required|exists:tipos_pago,id', // obligatorio
            'descuento_total'            => 'nullable|numeric|min:0',
            'con_factura'                => 'required|boolean',
        ]);

        try {
            DB::beginTransaction();

            $venta = Venta::create([
                'cliente_id'      => $request->cliente_id,
                'sucursal_id'     => $request->sucursal_id,
                'fecha'           => today()->toDateString(),
                'tipo_pago_id'    => $request->tipo_pago_id,
                'descuento_total' => $request->descuento_total ?? 0,
                'con_factura'     => (bool) ($request->con_factura ?? false),
                'total'           => 0,
                'estado'          => 'vigente', // por defecto
            ]);

            $totalFinal = 0;

            foreach ($request->productos as $p) {
                $cantidad  = $p['cantidad'];
                $precio    = $p['precio'];
                $descuento = $p['descuento'] ?? 0;
                $subtotal  = ($cantidad * $precio) - $descuento;
                $totalFinal += $subtotal;

                DetalleVenta::create([
                    'venta_id'       => $venta->id,
                    'producto_id'    => $p['producto_id'],
                    'cantidad'       => $cantidad,
                    'precio_unitario'=> $precio,
                    'descuento'      => $descuento,
                    'subtotal'       => $subtotal
                ]);

                // Kardex salida
                InventarioService::salidaNormal(
                    $request->sucursal_id,
                    $p['producto_id'],
                    $cantidad,
                    $precio,
                    'Venta',
                    $venta->id,
                    auth()->id()
                );
            }

            $venta->update([
                'total' => max($totalFinal - ($request->descuento_total ?? 0), 0)
            ]);

            DB::commit();
            return redirect()->route('ventas.index')->with('success', 'Venta registrada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar venta: ' . $e->getMessage());
        }
    }

    public function index()
    {
        $ventas = Venta::with(['cliente', 'sucursal', 'tipoPago'])
            ->orderByDesc('id')
            ->get();
        return view('ventas.index', compact('ventas'));
    }

    public function show($id)
    {
        $venta = Venta::with(['cliente', 'sucursal', 'tipoPago', 'detalles.producto'])
            ->findOrFail($id);
        return view('ventas.show', compact('venta'));
    }

    public function generarTicket($id)
    {
        $venta = Venta::with(['cliente', 'sucursal', 'tipoPago', 'detalles.producto'])
            ->findOrFail($id);
        $venta->fecha = Carbon::parse($venta->fecha);

        $pdf = Pdf::loadView('ventas.ticket', compact('venta'));
        return $pdf->stream("ticket_venta_{$venta->id}.pdf");
    }

    public function anular($id)
    {
        $venta = Venta::with('detalles')->findOrFail($id);

        if ($venta->estado === 'anulada') {
            return redirect()->route('ventas.index')->with('error', 'Esta venta ya fue anulada.');
        }

        DB::transaction(function () use ($venta) {
            foreach ($venta->detalles as $detalle) {
                InventarioService::entradaNormal(
                    $venta->sucursal_id,
                    $detalle->producto_id,
                    $detalle->cantidad,
                    $detalle->precio_unitario,
                    'Reversión Venta',
                    $venta->id,
                    auth()->id()
                );
            }

            $venta->update([
                'estado'      => 'anulada',
                'observacion' => 'Venta anulada el ' . now()->format('Y-m-d H:i'),
            ]);
        });

        return redirect()->route('ventas.index')->with('success', 'Venta anulada y stock devuelto.');
    }
}
