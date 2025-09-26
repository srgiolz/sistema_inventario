@extends('layouts.app')

@section('content')
<div class="container">
    
    <h4 class="mb-3">
        <i class="bi bi-arrow-left-right text-success"></i> 
        Historial de <span class="fw-bold">Traspasos</span>
    </h4>

    <div class="d-flex justify-content-between align-items-center mb-4">
        {{-- Botón Nuevo Traspaso --}}
        <a href="{{ route('traspasos.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Traspaso
        </a>

        {{-- Filtro de estados --}}
        <form method="GET" action="{{ route('traspasos.index') }}" class="d-flex align-items-center">
            <label for="estado" class="me-2 fw-semibold">Filtrar por:</label>
            <select name="estado" id="estado" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Todos</option>
                <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>
                    Pendientes ({{ $pendientesCount }})
                </option>
                <option value="confirmado_origen" {{ request('estado') == 'confirmado_origen' ? 'selected' : '' }}>
                    En tránsito
                </option>
                <option value="confirmado_destino" {{ request('estado') == 'confirmado_destino' ? 'selected' : '' }}>
                    Recibidos
                </option>
                <option value="rechazado" {{ request('estado') == 'rechazado' ? 'selected' : '' }}>
                    Rechazados
                </option>
                <option value="anulado" {{ request('estado') == 'anulado' ? 'selected' : '' }}>
                    Anulados
                </option>
            </select>
        </form>
    </div>

    @if($traspasos->isEmpty())
        <div class="alert alert-info shadow-sm">
            📭 No se encontraron traspasos en este estado.
        </div>
    @endif

    @foreach($traspasos as $t)
        <div class="card mb-4 border-0 shadow-sm rounded-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                <h5 class="mb-0">
                    <i class="bi bi-arrow-left-right text-success"></i> Traspaso #{{ $t->id }}
                </h5>
                <small class="text-muted">
                    <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($t->fecha)->format('d/m/Y') }} |
                    <strong>Tipo:</strong> {{ ucfirst($t->tipo) }} |
                    <strong>Estado:</strong>
                    @switch($t->estado)
                        @case('pendiente')
                            <span class="badge bg-warning text-dark">Pendiente (esperando envío)</span>
                            @break
                        @case('confirmado_origen')
                            <span class="badge bg-info text-dark">En tránsito</span>
                            @break
                        @case('confirmado_destino')
                            <span class="badge bg-success">Recibido</span>
                            @break
                        @case('rechazado')
                            <span class="badge bg-danger">Rechazado</span>
                            @break
                        @case('anulado')
                            <span class="badge bg-secondary">Anulado</span>
                            @break
                    @endswitch
                </small>
            </div>

            <div class="card-body">
                {{-- Sucursales --}}
                <div class="p-2 mb-3 bg-light rounded border-start border-3 border-info">
                    <i class="bi bi-shop text-info me-2"></i>
                    <strong>De:</strong> {{ $t->sucursalOrigen->nombre }} 
                    <i class="bi bi-arrow-right mx-2"></i>
                    <strong>A:</strong> {{ $t->sucursalDestino->nombre }}
                </div>

                {{-- Observación --}}
                @if ($t->observacion)
                    <div class="p-2 mb-3 bg-light rounded border-start border-3 border-primary">
                        <i class="bi bi-journal-text text-primary me-2"></i>
                        <strong>Observación:</strong> {{ $t->observacion }}
                    </div>
                @endif

                {{-- Motivo anulación/rechazo --}}
                @if (in_array($t->estado, ['anulado','rechazado']) && $t->motivo_anulacion)
                    <div class="p-2 mb-3 bg-light rounded border-start border-3 border-danger">
                        <i class="bi bi-x-circle text-danger me-2"></i>
                        <strong>Motivo:</strong> {{ $t->motivo_anulacion }}
                    </div>
                @endif

                {{-- Tabla de productos --}}
                <table class="table table-hover table-sm align-middle">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>Producto</th>
                            <th width="120">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($t->detalles as $detalle)
                            <tr>
                                <td>{{ $detalle->producto->codigo_item }} - {{ $detalle->producto->descripcion }}</td>
                                <td class="text-center fw-bold">{{ $detalle->cantidad }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Botones de acción --}}
                <div class="mt-3 d-flex flex-wrap gap-2">
                    <a href="{{ route('traspasos.revisar', $t->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye"></i> Revisar
                    </a>

                    @if($t->estado === 'confirmado_destino')
                        <a href="{{ route('traspasos.pdf', $t->id) }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                            <i class="bi bi-file-earmark-pdf"></i> Ver PDF
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
