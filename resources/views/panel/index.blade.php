@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-1"><i class="bi bi-bar-chart-fill text-primary"></i> <span class="fw-bold text-primary">Panel</span> <span class="fw-bold">Ejecutivo</span></h4>
    <p class="text-muted mb-4">Vista general del rendimiento actual del sistema de ventas.</p>

    {{-- 🎯 TARJETAS INFORMATIVAS --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card text-white bg-success rounded-4 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-box"></i> Productos</h6>
                    <h5 class="fw-bold">{{ $totalProductos }}</h5>
                    <small>Productos registrados</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-primary rounded-4 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-stack"></i> Total en stock</h6>
                    <h5 class="fw-bold">{{ $totalStock }}</h5>
                    <small>Total en stock</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-secondary rounded-4 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-people"></i> Clientes</h6>
                    <h5 class="fw-bold">{{ $totalClientes }}</h5>
                    <small>Clientes registrados</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning rounded-4 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-cash-stack"></i> Ventas acumuladas</h6>
                    <h5 class="fw-bold">Bs {{ number_format($totalVentasAcumuladas, 2) }}</h5>
                    <small>Ventas acumuladas</small>
                </div>
            </div>
        </div>
    </div>

    {{-- TARJETAS SECUNDARIAS --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-info rounded-4 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-calendar-day"></i> Ventas hoy</h6>
                    <h5 class="fw-bold">Bs {{ number_format($ventasDelDia, 2) }}</h5>
                    <small>Ventas del día</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger rounded-4 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title"><i class="bi bi-exclamation-triangle-fill"></i> Stock crítico</h6>
                    <h5 class="fw-bold">{{ $productosCriticos }}</h5>
                    <small>Poco stock</small>
                </div>
            </div>
        </div>
    </div>

    {{-- VENTAS POR SUCURSAL --}}
    <div class="card mb-4">
        <div class="card-header bg-light fw-bold">
            <i class="bi bi-geo-alt"></i> Ventas por Sucursal (Mes Actual)
        </div>
        <div class="card-body">
            <div class="row">
                @foreach ($ventasPorSucursal as $sucursal => $monto)
                    <div class="col-md-6">
                        <div class="card shadow-sm mb-3">
                            <div class="card-body text-center">
                                <div class="mb-2"><i class="bi bi-buildings"></i> <strong>{{ $sucursal }}</strong></div>
                                <div class="fw-bold">Bs {{ number_format($monto, 2) }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- RESUMEN MENSUAL --}}
    <div class="card mb-4">
        <div class="card-header bg-white fw-bold">
            <i class="bi bi-clipboard-data"></i> Resumen mensual
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-2"><div class="text-dark"><i class="bi bi-receipt-cutoff h3"></i></div><div class="fw-bold">{{ $resumenMensual['productosVendidos'] }}</div><small class="text-muted">Productos vendidos</small></div>
                <div class="col-md-2"><div class="text-dark"><i class="bi bi-cash-coin h3"></i></div><div class="fw-bold">Bs {{ number_format($resumenMensual['ventasMes'], 2) }}</div><small class="text-muted">Total monetario</small></div>
                <div class="col-md-2"><div class="text-dark"><i class="bi bi-credit-card h3"></i></div><div class="fw-bold">Bs {{ number_format($resumenMensual['mejorTicket'], 2) }}</div><small class="text-muted">Mejor ticket</small></div>
                <div class="col-md-2"><div class="text-dark"><i class="bi bi-calendar-date h3"></i></div><div class="fw-bold">{{ $resumenMensual['mejorDia'] ?? 'Sin datos' }}</div><small class="text-muted">Mejor día</small></div>
                <div class="col-md-2"><div class="text-dark"><i class="bi bi-bar-chart-steps h3"></i></div><div class="fw-bold">{{ number_format($resumenMensual['promedio'], 2) }}</div><small class="text-muted">Promedio por venta</small></div>
                <div class="col-md-2"><div class="text-dark"><i class="bi bi-person-check h3"></i></div><div class="fw-bold">{{ $resumenMensual['clientesUnicos'] }}</div><small class="text-muted">Clientes únicos</small></div>
            </div>
        </div>
    </div>

    {{-- GRÁFICOS --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4 h-100">
                <div class="card-header fw-bold bg-white"><i class="bi bi-bar-chart-line"></i> Ventas por Mes</div>
                <div class="card-body d-flex align-items-center justify-content-center" style="height: 350px;">
                    <canvas id="graficoVentasMes" class="w-100 h-100"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4 h-100">
                <div class="card-header fw-bold bg-white">
                    <i class="bi bi-credit-card-2-front"></i> Ventas por Tipo de Pago
                </div>
                <div class="card-body d-flex flex-column justify-content-between" style="height: 350px; overflow: hidden;">
                    <form id="formTipoPago"class="row g-2 mb-2">
                        <div class="col"><input type="date" name="desde" id="inputDesde" class="form-control" value="{{ $desde }}"></div>
                        <div class="col"><input type="date" name="hasta" id="inputHasta" class="form-control" value="{{ $hasta }}"></div>
                        <div class="col"><button type="button" id="btnFiltrarTipoPago" class="btn btn-primary w-100">Filtrar</button></div>
                    </form>
                    <div style="flex-grow: 1; position: relative;">
                        <canvas id="graficoTipoPago" style="max-height: 200px; width: 100%;"></canvas>
                    </div>
                    <div id="resumenTipoPago">
                    @include('panel.partials.ventas-por-tipo', ['ventasPorTipoPago' => $ventasPorTipoPago])
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TOP 10 PRODUCTOS MÁS VENDIDOS DEL MES --}}
    <div class="card shadow-sm mb-4 mt-4">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-award-fill text-dark"></i> Top 10 productos más vendidos del mes
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm align-middle text-center table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th class="text-start">Producto</th>
                        <th style="width: 120px;">Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topProductos as $index => $producto)
                        <tr>
                            <td class="fw-bold bg-light">{{ $index + 1 }}</td>
                            <td class="text-start" title="{{ $producto->descripcion }}">
                                {{ Str::limit($producto->descripcion, 50) }}
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success fw-semibold">
                                    {{ $producto->total_vendidos }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-muted">No hay ventas registradas este mes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-archive text-danger"></i> Productos sin ventas en los últimos 30 días
    </div>
    <div class="card-body">

        {{-- 🔎 Filtro por línea --}}
        <form method="GET" action="{{ route('panel.index') }}" class="row g-2 mb-3">
            <div class="col-md-4">
                <select name="linea_id" id="filtroLinea" class="form-select">
                    <option value="">— Ver todas las líneas —</option>
                    @foreach($lineas as $linea)
                        <option value="{{ $linea }}" {{ request('linea_id') == $linea ? 'selected' : '' }}>
                    {{ $linea }}
</option>
                    @endforeach
                </select>
            </div>
        </form>
        <div id="productosSinVentas">
        @if(!request('linea_id'))
            <div class="alert alert-info d-flex align-items-center" role="alert">
                <i class="bi bi-funnel-fill me-2"></i>
                <div>
                    Selecciona una línea para ver los productos sin ventas correspondientes.
                </div>
            </div>
        @else
        <div id="productosSinVentas">
            <div class="table-responsive">
                <table class="table table-sm align-middle text-center table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th class="text-start">Producto</th>
                            <th>Línea</th>
                            <th>Stock actual</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productosSinVentas30d as $index => $producto)
                            <tr>
                                <td class="bg-light fw-bold">{{ $index + 1 }}</td>
                                <td class="text-start" title="{{ $producto->descripcion }}">
                                    {{ Str::limit($producto->descripcion, 50) }}
                                </td>
                                <td>{{ $producto->linea->nombre ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-danger-subtle text-danger fw-semibold">
                                        {{ $producto->inventario->cantidad ?? 0 }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-muted">No se encontraron productos sin ventas en esta línea.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        </div>
    </div>
</div>
{{-- ÚLTIMOS MOVIMIENTOS DE STOCK --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-bold">
        <i class="bi bi-clock-history text-primary"></i> Últimos Movimientos de Stock
    </div>
    <div class="card-body">
        <form id="formFiltroStock" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="date" id="fechaDesdeMov" class="form-control" placeholder="Desde">
            </div>
            <div class="col-md-4">
                <input type="date" id="fechaHastaMov" class="form-control" placeholder="Hasta">
            </div>
            <div class="col-md-4">
                <button type="button" id="btnFiltrarMovimientos" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </form>

        <div id="tablaMovimientosStock">
    @include('panel.partials.movimientos-stock', ['movimientos' => $movimientos])
        </div>

    </div>
</div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const labels = @json($labels);
    const datos = @json($datos);
    if (labels.length > 0 && datos.length > 0) {
        new Chart(document.getElementById('graficoVentasMes'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Bs vendidos',
                    data: datos,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // ✅ Cambiamos este bloque:
    const pagos = @json($ventasPorTipoPago);
    if (Object.keys(pagos).length > 0) {
        const ctx = document.getElementById('graficoTipoPago').getContext('2d');

        if (graficoTipoPago) {
            graficoTipoPago.destroy(); // 🔥 Evita el error de Canvas duplicado
        }

        graficoTipoPago = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: Object.keys(pagos),
                datasets: [{
                    data: Object.values(pagos),
                    backgroundColor: ['#36A2EB', '#4BC0C0', '#FFCD56', '#FF6384']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
});
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const filtroLinea = document.getElementById('filtroLinea');
        if (filtroLinea) {
            filtroLinea.addEventListener('change', function () {
                const lineaSeleccionada = this.value;

                fetch(`{{ route('panel.filtrar-productos') }}?linea_id=${encodeURIComponent(lineaSeleccionada)}`)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('productosSinVentas').innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error al cargar productos sin ventas:', error);
                        alert('Hubo un error al filtrar los productos.');
                    });
            });
        }
    });
</script>
<script>
    let graficoTipoPago = null; // Variable global para controlar el gráfico

    document.addEventListener("DOMContentLoaded", function () {
    const btnFiltrar = document.getElementById('btnFiltrarTipoPago');
    const inputDesde = document.getElementById('inputDesde');
    const inputHasta = document.getElementById('inputHasta');

    if (btnFiltrar) {
        btnFiltrar.addEventListener('click', function () {
            const desde = inputDesde.value;
            const hasta = inputHasta.value;

            fetch(`{{ route('panel.filtrar-ventas-tipo') }}?desde=${desde}&hasta=${hasta}`)
                .then(response => response.json())
                .then(data => {
                    const destino = document.getElementById('resumenTipoPago');
                    if (destino) destino.innerHTML = data.html;

                    if (graficoTipoPago) graficoTipoPago.destroy();

                    const ctx = document.getElementById('graficoTipoPago').getContext('2d');
                    graficoTipoPago = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                data: data.datos,
                                backgroundColor: ['#36A2EB', '#4BC0C0', '#FFCD56', '#FF6384']
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    });
                })
                .catch(error => {
                    console.error('Error al filtrar por tipo de pago:', error);
                    alert('Ocurrió un error al filtrar los datos.');
                });
        });
    }
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const btnFiltrar = document.getElementById('btnFiltrarMovimientos');
    const inputDesde = document.getElementById('fechaDesdeMov');
    const inputHasta = document.getElementById('fechaHastaMov');
    const contenedor = document.getElementById('tablaMovimientosStock');

    if (btnFiltrar) {
        btnFiltrar.addEventListener('click', function () {
            const desde = inputDesde.value;
            const hasta = inputHasta.value;

            const url = `{{ route('panel.filtrar-movimientos') }}?desde=${desde}&hasta=${hasta}`;

            fetch(url)
                .then(response => response.text())
                .then(html => {
                    contenedor.innerHTML = html;
                })
                .catch(error => {
                    console.error("Error al filtrar movimientos:", error);
                    alert("Ocurrió un error al cargar los movimientos.");
                });
        });
    }
});
</script>
@endpush
