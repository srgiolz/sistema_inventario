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

        {{-- Botón Pendientes con contador --}}
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
                            <span class="badge bg-danger">Rechazado en destino</span>
                            @break
                        @case('anulado')
                            {{-- Diferenciar por fechas: si nunca tuvo confirmación = cancelado en origen --}}
                            @if ($t->fecha_confirmacion == null)
                                <span class="badge bg-secondary">Cancelado en origen</span>
                            @else
                                <span class="badge bg-secondary">Anulado en tránsito</span>
                            @endif
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

                {{-- Motivo de anulación o rechazo --}}
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
                    {{-- Revisar --}}
                    <a href="{{ url('/traspasos/' . $t->id . '/revisar') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye"></i> Revisar
                    </a>

                    {{-- Pendiente: origen puede confirmar o cancelar --}}
                    @if($t->estado === 'pendiente')
                        <form action="{{ route('traspasos.confirmarOrigen', $t->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-success btn-sm"
                                onclick="return confirm('¿Confirmar envío desde ORIGEN?')">
                                <i class="bi bi-check2-circle"></i> Confirmar envío
                            </button>
                        </form>

                        <a href="{{ route('traspasos.edit', $t->id) }}" class="btn btn-outline-warning btn-sm">
                            <i class="bi bi-pencil"></i> Editar
                        </a>

                        <form action="{{ route('traspasos.anular', $t->id) }}" method="POST" 
                              onsubmit="return confirmMotivo(this)" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-x-circle"></i> Cancelar en origen
                            </button>
                        </form>
                    @endif

                    {{-- En tránsito: destino puede confirmar recepción o rechazar --}}
                    @if($t->estado === 'confirmado_origen')
                        <form action="{{ route('traspasos.confirmarDestino', $t->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-success btn-sm"
                                onclick="return confirm('¿Confirmar recepción en DESTINO?')">
                                <i class="bi bi-check2-circle"></i> Confirmar recepción
                            </button>
                        </form>

                        <form action="{{ route('traspasos.rechazar', $t->id) }}" method="POST" 
                              onsubmit="return confirmMotivo(this)" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-x-circle"></i> Rechazar en destino
                            </button>
                        </form>
                    @endif

                    {{-- Confirmado destino: solo PDF --}}
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

@push('scripts')
<script>
function confirmMotivo(form) {
    let motivo = prompt("Ingrese el motivo:");
    if (!motivo) {
        alert("Debe ingresar un motivo.");
        return false;
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




