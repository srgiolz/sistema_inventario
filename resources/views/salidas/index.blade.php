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

                        @if($salida->estado === 'anulado' && !$salida->fecha_confirmacion)
                            Cancelado
                        @elseif($salida->estado === 'anulado' && $salida->fecha_confirmacion)
                            Anulado
                        @else
                            {{ ucfirst($salida->estado) }}
                        @endif
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
                        <strong>Motivo {{ $salida->fecha_confirmacion ? 'de anulación' : 'de cancelación' }}:</strong> 
                        {{ $salida->motivo_anulacion }}
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
                                <td>{{ $detalle->producto->codigo_item }} - {{ $detalle->producto->descripcion }}</td>
                                <td class="text-center fw-bold">{{ $detalle->cantidad }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Botones de acción --}}
                <div class="mt-3 d-flex flex-wrap gap-2">
                    {{-- Editar + Confirmar solo si está pendiente --}}
                    @if($salida->estado === 'pendiente')
                        <a href="{{ route('salidas.edit', $salida->id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-pencil-square"></i> Editar
                        </a>

                        <form action="{{ route('salidas.confirm', $salida->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-success btn-sm"
                                onclick="return confirm('¿Confirmar esta salida? Se actualizará el stock.')">
                                <i class="bi bi-check2-circle"></i> Confirmar
                            </button>
                        </form>

                        <form action="{{ route('salidas.anular', $salida->id) }}" method="POST" 
                              onsubmit="return confirmMotivo(this)" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </button>
                        </form>
                    @endif

                    {{-- Anular solo si está confirmado --}}
                    @if($salida->estado === 'confirmado')
                        <form action="{{ route('salidas.anular', $salida->id) }}" method="POST" 
                              onsubmit="return confirmMotivo(this)" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-x-circle"></i> Anular
                            </button>
                        </form>
                    @endif

                    {{-- PDF siempre disponible --}}
                    <a href="{{ route('salidas.pdf', $salida->id) }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                        <i class="bi bi-file-earmark-pdf"></i> Ver PDF
                    </a>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
function confirmMotivo(form) {
    let motivo = prompt("Ingrese el motivo:");
    if (!motivo) {
        alert("Debe ingresar un motivo.");
        return false; // bloquea el submit si no hay motivo
    }
    let input = document.createElement("input");
    input.type = "hidden";
    input.name = "motivo_anulacion";
    input.value = motivo;
    form.appendChild(input);
    return true;
}
</script>
@endpush
