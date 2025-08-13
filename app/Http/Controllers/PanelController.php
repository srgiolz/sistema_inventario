<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Inventario;
use App\Models\Sucursal;
use App\Models\Entrada;
use App\Models\Salida;
use App\Models\Traspaso;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PanelController extends Controller
{
    public function index(Request $request)
    {
        $hoy = Carbon::today();

        // 1️⃣ TARJETAS SUPERIORES
        $totalProductos = Producto::count();
        $totalStock = Inventario::sum('cantidad');
        $totalClientes = Cliente::count();
        $totalVentasAcumuladas = Venta::sum('total');
        $ventasDelDia = Venta::whereDate('created_at', $hoy)->sum('total');
        $productosCriticos = Inventario::where('cantidad', '<=', 2)->count();

        // 2️⃣ VENTAS DEL MES ACTUAL POR SUCURSAL
        $ventasPorSucursal = DB::table('ventas')
            ->join('sucursales', 'ventas.sucursal_id', '=', 'sucursales.id')
            ->select('sucursales.nombre', DB::raw('SUM(ventas.total) as total'))
            ->whereMonth('ventas.created_at', now()->month)
            ->groupBy('sucursales.nombre')
            ->pluck('total', 'sucursales.nombre');

        // 3️⃣ CUADRO RESUMEN MENSUAL
        $mesActual = now()->month;
        $mesAnterior = now()->copy()->subMonth()->month;

        $ventasMesActual = Venta::whereMonth('created_at', $mesActual);
        $ventasMesAnterior = Venta::whereMonth('created_at', $mesAnterior);

        $mejorTicket = $ventasMesActual->max('total');
        $mejorDia = Venta::selectRaw('DATE(created_at) as fecha, SUM(total) as total')
            ->whereMonth('created_at', $mesActual)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderByDesc('total')
            ->first();

        $promedioPorVenta = $ventasMesActual->count() > 0
            ? $ventasMesActual->sum('total') / $ventasMesActual->count()
            : 0;

        $productosVendidosMes = DetalleVenta::join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')  // Cambié 'id_venta' por 'venta_id'
            ->whereMonth('ventas.created_at', $mesActual)
            ->sum('detalle_ventas.cantidad');

        $comparativaMesAnterior = $ventasMesAnterior->sum('total') > 0
            ? (($ventasMesActual->sum('total') - $ventasMesAnterior->sum('total')) / $ventasMesAnterior->sum('total')) * 100
            : null;

        $clientesUnicosMes = Venta::whereMonth('created_at', $mesActual)
            ->distinct('cliente_id')
            ->count('cliente_id');

        $resumenMensual = [
            'mejorTicket' => $mejorTicket,
            'mejorDia' => $mejorDia ? $mejorDia->fecha : null,
            'promedio' => $promedioPorVenta,
            'productosVendidos' => $productosVendidosMes,
            'comparativaMesAnterior' => $comparativaMesAnterior,
            'clientesUnicos' => $clientesUnicosMes,
            'ventasMes' => $ventasMesActual->sum('total'),
        ];

        // 4️⃣ VENTAS POR TIPO DE PAGO (filtro de fecha)
        $desde = $request->input('desde') ?? Carbon::now()->startOfMonth()->toDateString();
        $hasta = $request->input('hasta') ?? Carbon::now()->endOfMonth()->toDateString();

        $ventasPorTipoPago = Venta::join('tipos_pago', 'ventas.tipo_pago_id', '=', 'tipos_pago.id')  // Cambié 'tipo_pago' por 'tipo_pago_id'
            ->whereBetween('ventas.created_at', [$desde, $hasta])
            ->where('tipos_pago.activo', 1)
            ->select('tipos_pago.nombre as tipo_pago', DB::raw('SUM(ventas.total) as total'))
            ->groupBy('tipos_pago.nombre')
            ->pluck('total', 'tipo_pago');

        // 5️⃣ GRÁFICO DE BARRAS - VENTAS POR MES (AÑO ACTUAL)
        $ventasPorMes = Venta::select(
            DB::raw('MONTH(created_at) as mes'),
            DB::raw('SUM(total) as total')
        )
        ->whereYear('created_at', now()->year)
        ->groupBy('mes')
        ->orderBy('mes')
        ->get();

        $labels = $ventasPorMes->pluck('mes')->map(function ($m) {
            return Carbon::create()->month($m)->format('F');
        });
        $datos = $ventasPorMes->pluck('total');

        // 6️⃣ TOP PRODUCTOS MÁS VENDIDOS DEL MES
        $topProductos = DetalleVenta::join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')  // Cambié 'producto_id' por 'producto_id'
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')
            ->whereMonth('ventas.created_at', now()->month)
            ->select('productos.descripcion', DB::raw('SUM(detalle_ventas.cantidad) as total_vendidos'))
            ->groupBy('productos.descripcion')
            ->orderByDesc('total_vendidos')
            ->limit(10)
            ->get();

        // 7️⃣ PANEL OPERATIVO
        $lineaId = $request->input('linea_id');

        $lineas = Producto::select('linea')
            ->distinct()
            ->whereNotNull('linea')
            ->orderBy('linea')
            ->pluck('linea');

        $productosSinVentas30d = Producto::whereNotIn('id', function ($query) {
            $query->select('producto_id')  // Cambié 'producto_id' por 'producto_id'
                ->from('detalle_ventas')
                ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')  // Cambié 'id_venta' por 'venta_id'
                ->where('ventas.created_at', '>=', now()->subDays(30));
        });

        if ($lineaId) {
            $productosSinVentas30d->where('linea', $lineaId);
        }

        $productosSinVentas30d = $productosSinVentas30d
            ->with('inventarios')
            ->orderBy('descripcion')
            ->get();

        $productosMayorRotacion = DetalleVenta::join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')  // Cambié 'producto_id' por 'producto_id'
            ->select('productos.descripcion', DB::raw('SUM(cantidad) as total_vendidos'))
            ->groupBy('productos.descripcion')
            ->orderByDesc('total_vendidos')
            ->limit(10)
            ->get();

        $movimientos = collect()
            ->merge(Entrada::latest()->take(5)->get())
            ->merge(Salida::latest()->take(5)->get())
            ->merge(Traspaso::latest()->take(5)->get())
            ->sortByDesc('created_at')
            ->take(10);

        return view('panel.index', compact(
            'totalProductos',
            'totalStock',
            'totalClientes',
            'totalVentasAcumuladas',
            'ventasDelDia',
            'productosCriticos',
            'ventasPorSucursal',
            'resumenMensual',
            'ventasPorTipoPago',
            'labels',
            'datos',
            'topProductos',
            'productosMayorRotacion',
            'productosSinVentas30d',
            'movimientos',
            'desde',
            'hasta',
            'lineas',
            'lineaId'
        ));
    }

    public function filtrarProductosSinVentas(Request $request)
    {
        $lineaId = $request->input('linea_id');

        $productos = Producto::whereNotIn('id', function ($query) {
            $query->select('producto_id')  // Cambié 'producto_id' por 'producto_id'
                ->from('detalle_ventas')
                ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.id')  // Cambié 'id_venta' por 'venta_id'
                ->where('ventas.created_at', '>=', now()->subDays(30));
        });

        if ($lineaId) {
            $productos->where('linea', $lineaId);
        }

        $productosSinVentas30d = $productos
            ->with('inventarios')
            ->orderBy('descripcion')
            ->get();

        return view('panel.partials.productos-sin-venta', compact('productosSinVentas30d'));
    }

    public function filtrarVentasPorTipo(Request $request)
    {
        $desde = $request->input('desde') ?? now()->startOfMonth()->toDateString();
        $hasta = $request->input('hasta') ?? now()->endOfMonth()->toDateString();

        $ventasPorTipoPago = \App\Models\Venta::join('tipos_pago', 'ventas.tipo_pago_id', '=', 'tipos_pago.id')  // Cambié 'tipo_pago_id' por 'tipo_pago'
            ->whereBetween('ventas.created_at', [$desde, $hasta])
            ->where('tipos_pago.activo', 1)
            ->select('tipos_pago.nombre as tipo_pago', DB::raw('SUM(ventas.total) as total'))
            ->groupBy('tipos_pago.nombre')
            ->pluck('total', 'tipo_pago');

        $resumenHtml = view('panel.partials.ventas-por-tipo', compact('ventasPorTipoPago'))->render();

        return response()->json([
            'html' => $resumenHtml,
            'labels' => $ventasPorTipoPago->keys(),
            'datos' => $ventasPorTipoPago->values()
        ]);
    }

    public function filtrarMovimientos(Request $request)
    {
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        $movimientos = \App\Models\MovimientoStock::query()
            ->when($desde, fn($q) => $q->whereDate('fecha', '>=', $desde))
            ->when($hasta, fn($q) => $q->whereDate('fecha', '<=', $hasta))
            ->latest()
            ->limit(30)
            ->get()
            ->map(function ($m) {
                return (object)[
                    'fecha' => $m->fecha,
                    'tipo' => $m->tipo,
                    'producto' => $m->producto->descripcion ?? '—',
                    'cantidad' => $m->cantidad,
                    'sucursal' => $m->sucursal->nombre ?? '—',
                ];
            });

        return view('panel.partials.movimientos-stock', compact('movimientos'));
    }
}
