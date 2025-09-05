@extends('layouts.app')

@section('content')
<div class="container">

    <h2 class="mb-4">
        <i class="bi bi-journal-text text-primary"></i> Kardex de Inventario
    </h2>

    <!-- 🔎 Filtros -->
    <form method="GET" action="{{ route('kardex.index') }}" class="row mb-4 g-3">
        <div class="col-md-4">
            <label class="form-label">Producto</label>
            <select name="producto_id" class="form-control">
                <option value="">-- Todos --</option>
                @foreach($productos as $p)
                    <option value="{{ $p->id }}" {{ request('producto_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->codigo_item }} - {{ $p->descripcion }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Sucursal</label>
            <select name="sucursal_id" class="form-control">
                <option value="">-- Todas --</option>
                @foreach($sucursales as $s)
                    <option value="{{ $s->id }}" {{ request('sucursal_id') == $s->id ? 'selected' : '' }}>
                        {{ $s->nombre }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary shadow-sm">
                <i class="bi bi-search"></i> Filtrar
            </button>
        </div>
    </form>

    <!-- 📊 Tabla de Kardex -->
    <div class="table-responsive shadow-sm">
        <table class="table table-striped table-hover align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Sucursal</th>
                    <th>Movimiento</th>
                    <th class="text-success">Entrada</th>
                    <th class="text-danger">Salida</th>
                    <th>Saldo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kardex as $mov)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y H:i') }}</td>
                        <td class="text-start">
                            <strong>{{ $mov->producto->codigo_item }}</strong><br>
                            <small class="text-muted">{{ $mov->producto->descripcion }}</small>
                        </td>
                        <td>{{ $mov->sucursal->nombre }}</td>
                        <td>
                            @php
                                switch ($mov->tipo_movimiento) {
                                    case 'ENTRADA': $label = 'Entrada'; $class = 'badge bg-success'; break;
                                    case 'SALIDA': $label = 'Salida'; $class = 'badge bg-danger'; break;
                                    case 'ANULACION_ENTRADA': $label = 'Anulación de Entrada'; $class = 'badge bg-warning text-dark'; break;
                                    case 'ANULACION_SALIDA': $label = 'Anulación de Salida'; $class = 'badge bg-warning text-dark'; break;
                                    case 'TRASPASO_IN': $label = 'Traspaso (Ingreso)'; $class = 'badge bg-info'; break;
                                    case 'TRASPASO_OUT': $label = 'Traspaso (Egreso)'; $class = 'badge bg-info'; break;
                                    default: $label = $mov->tipo_movimiento; $class = 'badge bg-secondary'; break;
                                }
                            @endphp
                            <span class="{{ $class }}">{{ $label }}</span><br>
                            <small class="text-muted">{{ $mov->doc_ref }}</small>
                        </td>

                        <!-- Entrada -->
                        <td class="text-success fw-bold">
                            @if(str_starts_with(strtolower($mov->tipo_movimiento), 'entrada') || str_contains(strtolower($mov->tipo_movimiento), 'traspaso_in'))
                                {{ intval($mov->cantidad) == $mov->cantidad ? intval($mov->cantidad) : number_format($mov->cantidad, 2) }}
                            @endif
                        </td>

                        <!-- Salida -->
                        <td class="text-danger fw-bold">
                            @if(str_starts_with(strtolower($mov->tipo_movimiento), 'salida') || str_contains(strtolower($mov->tipo_movimiento), 'anulacion'))
                                {{ intval(abs($mov->cantidad)) == abs($mov->cantidad) ? intval(abs($mov->cantidad)) : number_format(abs($mov->cantidad), 2) }}
                            @endif
                        </td>

                        <!-- Saldo -->
                        <td class="fw-bold">
                            {{ intval($mov->stock_final) == $mov->stock_final ? intval($mov->stock_final) : number_format($mov->stock_final, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">📭 No se registraron movimientos en el Kardex.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- 📌 Paginación -->
    <div class="mt-3">
        {{ $kardex->links() }}
    </div>
</div>
@endsection
