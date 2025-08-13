@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 1100px;">

    {{-- Encabezado + botón al historial --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-arrow-left-right text-success"></i>
            <span class="fw-semibold">Revisión del Traspaso #{{ $traspaso->id }}</span>
            @if ($traspaso->estado == 'pendiente')
                <span class="badge bg-warning text-dark">Pendiente</span>
            @elseif ($traspaso->estado == 'confirmado')
                <span class="badge bg-success">Confirmado</span>
            @else
                <span class="badge bg-danger">Rechazado</span>
            @endif
        </h4>

        <a href="{{ route('traspasos.index') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-clock-history me-1"></i> Historial de Traspasos
        </a>
    </div>

    {{-- Resumen en tarjetas --}}
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-semibold text-muted mb-3">Sucursales</h6>
                    <div class="mb-2">
                        <span class="text-muted">Origen:</span>
                        <span class="fw-semibold">{{ $traspaso->sucursalOrigen->nombre }}</span>
                    </div>
                    <div>
                        <span class="text-muted">Destino:</span>
                        <span class="fw-semibold">{{ $traspaso->sucursalDestino->nombre }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-semibold text-muted mb-3">Detalle</h6>
                    <div class="mb-2">
                        <span class="text-muted">Fecha:</span>
                        <span class="fw-semibold">{{ \Carbon\Carbon::parse($traspaso->fecha)->format('d/m/Y') }}</span>
                    </div>
                    <div>
                        <span class="text-muted">Observación:</span>
                        <span class="fw-semibold">{{ $traspaso->observacion ?? 'Ninguna' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Productos enviados --}}
    <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
        <h6 class="fw-semibold mb-0">
            <i class="bi bi-box-seam text-warning me-1"></i>
            Productos enviados
        </h6>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th style="width: 18%">Código</th>
                            <th>Descripción</th>
                            <th style="width: 12%">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($traspaso->detalles as $detalle)
                            <tr>
                                <td class="text-nowrap">{{ $detalle->producto->codigo_item }}</td>
                                <td>{{ $detalle->producto->descripcion }}</td>
                                <td class="text-center fw-semibold">{{ $detalle->cantidad }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Barra de acciones --}}
    <div class="d-flex justify-content-between align-items-center mt-4">
        <a href="{{ route('traspasos.pdf', $traspaso->id) }}" target="_blank" class="btn btn-outline-secondary">
            <i class="bi bi-printer me-1"></i> Imprimir Guía (PDF)
        </a>

        <div class="d-flex align-items-center gap-2">
            @if($traspaso->estado == 'pendiente')
                {{-- Formularios ocultos --}}
                <form id="form-confirmar" method="POST" action="{{ route('traspasos.confirmar', $traspaso->id) }}">
                    @csrf
                    @method('PATCH')
                </form>
                <form id="form-rechazar" method="POST" action="{{ route('traspasos.rechazar', $traspaso->id) }}">
                    @csrf
                    @method('PATCH')
                </form>

                <button type="button" class="btn btn-success" onclick="confirmarTraspaso()">
                    <i class="bi bi-check2-circle me-1"></i> Confirmar recepción
                </button>
                <button type="button" class="btn btn-danger" onclick="rechazarTraspaso()">
                    <i class="bi bi-x-circle me-1"></i> Rechazar
                </button>
            @endif

            <a href="{{ route('traspasos.index') }}" class="btn btn-outline-secondary">
                Volver
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmarTraspaso() {
    Swal.fire({
        title: '¿Confirmar recepción?',
        text: 'Esto actualizará el stock de la sucursal destino.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) document.getElementById('form-confirmar').submit();
    });
}

function rechazarTraspaso() {
    Swal.fire({
        title: '¿Rechazar traspaso?',
        text: 'Esto revertirá el stock al origen (no moverá inventario si ya fue confirmado).',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, rechazar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) document.getElementById('form-rechazar').submit();
    });
}
</script>
@endpush

