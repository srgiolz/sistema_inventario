<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class VentaController extends Controller
{
    public function create()
    {
        return view('ventas.create', [
            'clientes' => Cliente::all(),
            'productos' => Producto::with('inventarios')->get(),
            'sucursales' => Sucursal::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_cliente' => 'required|exists:clientes,id',
            'id_sucursal' => 'required|exists:sucursales,id',
            'productos' => 'required|array',
            'productos.*.id_producto' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|numeric|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
            'productos.*.descuento' => 'nullable|numeric|min:0',
            'tipo_pago' => 'required|string',
            'descuento_total' => 'nullable|numeric|min:0',
            'con_factura' => 'required|boolean',
        ]);

        try {
            DB::beginTransaction();

            $venta = Venta::create([
                'cliente_id' => $request->id_cliente,
                'sucursal_id' => $request->id_sucursal,
                'fecha' => now(),
                'tipo_pago' => $request->tipo_pago,
                'descuento_total' => $request->descuento_total ?? 0,
                'con_factura' => $request->con_factura ?? false,
                'total' => 0
            ]);

            $totalFinal = 0;

            foreach ($request->productos as $producto) {
                $cantidad = $producto['cantidad'];
                $precio = $producto['precio'];
                $descuento = $producto['descuento'] ?? 0;
                $subtotal = ($cantidad * $precio) - $descuento;
                $totalFinal += $subtotal;

                DetalleVenta::create([
                    'id_venta' => $venta->id,
                    'id_producto' => $producto['id_producto'],
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'descuento' => $descuento,
                    'subtotal' => $subtotal
                ]);

                $inventario = Inventario::where('id_producto', $producto['id_producto'])
                    ->where('id_sucursal', $request->id_sucursal)
                    ->first();

                if ($inventario && $inventario->cantidad >= $cantidad) {
                    $inventario->cantidad -= $cantidad;
                    $inventario->save();
                } else {
                    throw new \Exception("No hay suficiente stock para el producto ID {$producto['id_producto']}");
                }
            }

            $venta->update([
                'total' => max($totalFinal - ($request->descuento_total ?? 0), 0)
            ]);

            DB::commit();
            return redirect()->route('ventas.create')->with('success', 'Venta registrada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al registrar venta: ' . $e->getMessage());
        }
    }

   public function index()
   {
    $ventas = Venta::with(['cliente', 'sucursal'])->orderByDesc('id')->get();
    return view('ventas.index', compact('ventas'));
   }


    public function show($id)
    {
        $venta = Venta::with(['cliente', 'sucursal', 'detalles.producto'])->findOrFail($id);
        return view('ventas.show', compact('venta'));
    }

    public function generarTicket($id)
    {
        $venta = Venta::with(['cliente', 'sucursal', 'detalles.producto'])->findOrFail($id);
        $venta->fecha = Carbon::parse($venta->fecha); // 👈 convierte string en objeto Carbon

        $pdf = Pdf::loadView('ventas.ticket', compact('venta'));

        return $pdf->stream("ticket_venta_{$venta->id}.pdf");
    }
}

