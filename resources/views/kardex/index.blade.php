@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">📒 Kardex de Inventario</h1>

    <!-- Filtros -->
    <form method="GET" action="{{ route('kardex.index') }}" class="row mb-4">
        <div class="col-md-4">
            <label>Producto</label>
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
            <label>Sucursal</label>
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
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </div>
    </form>

    <!-- Tabla de Kardex -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Sucursal</th>
                    <th>Tipo Movimiento</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Saldo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kardex as $mov)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y H:i') }}</td>
                        <td>{{ $mov->producto->codigo_item }} - {{ $mov->producto->descripcion }}</td>
                        <td>{{ $mov->sucursal->nombre }}</td>
                        <td>
                            @php
                                $color = match(strtolower($mov->tipo_movimiento)) {
                                    'entrada' => 'text-success',
                                    'salida' => 'text-danger',
                                    'traspaso' => 'text-warning',
                                    default => 'text-secondary'
                                };
                            @endphp
                            <span class="{{ $color }}">
                                {{ ucfirst($mov->tipo_movimiento) }}
                                @if($mov->documento_tipo)
                                    <small class="d-block text-muted">
                                        ({{ $mov->documento_tipo }})
                                    </small>
                                @endif
                            </span>
                        </td>
                        <td class="text-success">
                            {{ strtolower($mov->tipo_movimiento) === 'entrada' ? $mov->cantidad : '' }}
                        </td>
                        <td class="text-danger">
                            {{ strtolower($mov->tipo_movimiento) === 'salida' ? abs($mov->cantidad) : '' }}
                        </td>
                        <td>{{ $mov->stock_final }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Sin movimientos en el Kardex.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-3">
        {{ $kardex->links() }}
    </div>
</div>
@endsection
