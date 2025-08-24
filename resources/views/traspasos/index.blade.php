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

        {{-- Botón Pendientes con contador fijo en rojo --}}
        <a href="{{ route('traspasos.pendientes') }}" 
           class="btn btn-sm btn-outline-warning shadow-sm" 
           data-bs-toggle="tooltip" 
           data-bs-placement="left" 
           title="Ver traspasos pendientes">
            <i class="bi bi-hourglass-split me-1"></i> Pendientes
            @if($pendientesCount > 0)
                <span class="badge bg-danger ms-1">
                    {{ $pendientesCount }}
                </span>
            @endif
        </a>
    </div>

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
                    @if($t->estado == 'pendiente')
                        <span class="badge bg-warning text-dark">Pendiente</span>
                    @elseif($t->estado == 'confirmado')
                        <span class="badge bg-success">Confirmado</span>
                    @else
                        <span class="badge bg-danger">Rechazado</span>
                    @endif
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
                    {{-- Revisar --}}
                    <a href="{{ url('/traspasos/' . $t->id . '/revisar') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye"></i> Revisar
                    </a>

                    {{-- Editar (si está pendiente) --}}
                    @if($t->estado == 'pendiente')
                        <a href="{{ route('traspasos.edit', $t->id) }}" class="btn btn-outline-warning btn-sm">
                            <i class="bi bi-pencil"></i> Editar
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush




