@extends('layouts.app')
@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="container">

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
                    <i class="bi bi-box-seam text-danger"></i> Salida #{{ $salida->id }}
                </h5>
                <div>
                    {{-- Badge de estado --}}
                    <span class="badge 
                        @if($salida->estado === 'pendiente') bg-warning text-dark
                        @elseif($salida->estado === 'confirmado') bg-success
                        @elseif($salida->estado === 'anulado') bg-danger
                        @endif">
                        {{ ucfirst($salida->estado) }}
                    </span>
                    <small class="text-muted ms-2">
                        <strong>Sucursal:</strong> {{ $salida->sucursal->nombre }} |
                        <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($salida->fecha)->format('d/m/Y') }} |
                        <strong>Tipo:</strong> {{ $salida->tipo ?? 'No especificado' }}
                    </small>
                </div>
            </div>

            <div class="card-body">
                @if ($salida->motivo)
                    <div class="p-2 mb-2 bg-light rounded border-start border-3 border-warning">
                        <i class="bi bi-info-circle text-warning me-2"></i>
                        <strong>Motivo:</strong> {{ $salida->motivo }}
                    </div>
                @endif

                @if ($salida->observacion)
                    <div class="p-2 mb-2 bg-light rounded border-start border-3 border-primary">
                        <i class="bi bi-journal-text text-primary me-2"></i>
                        <strong>Observación:</strong> {{ $salida->observacion }}
                    </div>
                @endif

                @if ($salida->estado === 'anulado' && $salida->motivo_anulacion)
                    <div class="p-2 mb-2 bg-light rounded border-start border-3 border-danger">
                        <i class="bi bi-x-octagon text-danger me-2"></i>
                        <strong>Motivo de anulación:</strong> {{ $salida->motivo_anulacion }}
                    </div>
                @endif

                {{-- Tabla de productos --}}
                <table class="table table-hover table-sm align-middle">
                    <thead class="table-danger text-center">
                        <tr>
                            <th>Producto</th>
                            <th width="120">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salida->detalles as $detalle)
                            <tr>
                                <td style="white-space: normal; word-break: break-word;">
                                    {{ $detalle->producto->codigo_item }} - {{ $detalle->producto->descripcion }}
                                </td>
                                <td class="text-center fw-bold">{{ $detalle->cantidad }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Solo botón revisar --}}
                <div class="mt-3 d-flex flex-wrap gap-2">
                    <a href="{{ route('salidas.revisar', $salida->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye"></i> Revisar
                    </a>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
