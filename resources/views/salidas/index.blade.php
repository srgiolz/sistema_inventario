@extends('layouts.app')

@section('content')
<div class="container">

    {{-- 🔔 Mensajes flash --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <h4 class="mb-3">
        <i class="bi bi-box-arrow-up text-danger"></i> 
        Historial de <span class="fw-bold">Salidas</span>
    </h4>

    <a href="{{ route('salidas.create') }}" class="btn btn-primary mb-4 shadow-sm">
        <i class="bi bi-plus-circle me-1"></i> Nueva Salida
    </a>

    @foreach($salidas as $salida)
        <div class="card mb-4 border-0 shadow-sm rounded-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                <h5 class="mb-0">
                    <i class="bi bi-box-arrow-up text-danger"></i> Salida #{{ $salida->id }}
                </h5>
                <small class="text-muted">
                    <strong>Sucursal:</strong> {{ $salida->sucursal->nombre }} |
                    <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($salida->fecha)->format('d/m/Y') }} |
                    <strong>Tipo:</strong> {{ $salida->tipo ?? 'No especificado' }}
                </small>
            </div>

            <div class="card-body">
                @if ($salida->motivo)
                    <div class="p-2 mb-3 bg-light rounded border-start border-3 border-warning">
                        <i class="bi bi-info-circle text-warning me-2"></i>
                        <strong>Motivo:</strong> {{ $salida->motivo }}
                    </div>
                @endif

                @if ($salida->observacion)
                    <div class="p-2 mb-3 bg-light rounded border-start border-3 border-primary">
                        <i class="bi bi-journal-text text-primary me-2"></i>
                        <strong>Observación:</strong> {{ $salida->observacion }}
                    </div>
                @endif

                <table class="table table-hover table-sm align-middle">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>Producto</th>
                            <th width="120">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salida->detalles as $detalle)
                            <tr>
                                <td>{{ $detalle->producto->codigo_item }} - {{ $detalle->producto->descripcion }}</td>
                                <td class="text-center fw-bold">{{ $detalle->cantidad }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Botones de acción --}}
                <div class="mt-3 d-flex flex-wrap gap-2">
                    {{-- PDF --}}
                    <a href="{{ route('salidas.pdf', $salida->id) }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                        <i class="bi bi-file-earmark-pdf"></i> Ver PDF
                    </a>

                    {{-- Reversar --}}
                    <form action="{{ route('salidas.reversar', $salida->id) }}" method="POST" onsubmit="return confirm('¿Deseas reversar esta salida? Esta acción no puede deshacerse.')" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-arrow-counterclockwise"></i> Reversar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection

