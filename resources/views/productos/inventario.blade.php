@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">📦 Inventario de Productos</h2>

    {{-- FILTROS DE BÚSQUEDA --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('productos.inventario') }}">
                <div class="row g-2">
                    <div class="col-md-2">
                        <input type="text" name="codigo" class="form-control form-control-sm" placeholder="Código" value="{{ request('codigo') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="descripcion" class="form-control form-control-sm" placeholder="Descripción" value="{{ request('descripcion') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="linea" class="form-select form-select-sm">
                            <option value="">Todas las líneas</option>
                            @foreach($lineas as $linea)
                                <option value="{{ $linea }}" {{ request('linea') == $linea ? 'selected' : '' }}>{{ $linea }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100">Filtrar</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('productos.inventario') }}" class="btn btn-sm btn-secondary w-100">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLA DE INVENTARIO --}}
    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle small">
                <thead class="table-dark">
                    <tr>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Línea</th>
                        <th>Familia</th>
                        <th>Unidad</th>
                        <th>Talla</th>
                        @foreach($sucursales as $sucursal)
                            <th title="{{ $sucursal->nombre }}">{{ Str::limit($sucursal->nombre, 3, '') }}</th>
                        @endforeach
                        <th>Total</th>
                        <th>Precio Costo</th>
                        <th>Precio Venta</th>
                        <th>Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productos as $producto)
                        @php
                            $total = 0;
                            $stock_por_sucursal = [];
                            foreach($sucursales as $sucursal) {
                                $cantidad = $producto->inventarios->firstWhere('sucursal_id', $sucursal->id)->cantidad ?? 0;
                                $total += $cantidad;
                                $stock_por_sucursal[] = $cantidad;
                            }
                        @endphp
                        <tr>
                            <td>{{ $producto->codigo_item }}</td>
                            <td>{{ $producto->descripcion }}</td>
                            <td>{{ $producto->linea }}</td>
                            <td>{{ $producto->familia }}</td>
                            <td>{{ $producto->unidad_medida }}</td>
                            <td>{{ $producto->talla }}</td>

                            {{-- Stock por sucursal --}}
                            @foreach($sucursales as $sucursal)
                                @php
                                    $stock = $producto->inventarios->firstWhere('sucursal_id', $sucursal->id)->cantidad ?? 0;
                                    $class = '';
                                    if ($stock == 0) {
                                        $class = 'text-danger fw-bold';
                                    } elseif ($stock <= 5) {
                                        $class = 'text-warning fw-bold';
                                    } elseif ($stock <= 20) {
                                        $class = 'text-primary fw-bold';
                                    } else {
                                        $class = 'text-success fw-bold';
                                    }
                                @endphp
                                <td class="text-center {{ $class }}">{{ $stock == 0 ? 'Sin stock' : $stock }}</td>
                            @endforeach

                            <td class="fw-bold text-center">{{ $total }}</td>
                            <td class="fw-bold">Bs {{ number_format($producto->precio_costo, 2) }}</td>
                            <td class="fw-bold">Bs {{ number_format($producto->precio_venta, 2) }}</td>
                            <td>
                                <a href="{{ route('productos.edit', $producto->id) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('productos.destroy', $producto->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este producto?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Estilos --}}
<style>
    table td, table th {
        padding: 4px 6px !important;
        font-size: 13px !important;
    }
</style>

{{-- DataTables para ordenamiento --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function () {
        $('.table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            },
            "order": [[ {{ 6 + $sucursales->count() }}, "desc" ]]
        });
    });
</script>
@endsection

