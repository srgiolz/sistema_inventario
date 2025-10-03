@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 1100px;">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-arrow-left-right text-success"></i>
            <span class="fw-semibold">Revisión del Traspaso #{{ $traspaso->id }}</span>

            @if ($traspaso->estado == 'pendiente')
                <span class="badge bg-warning text-dark">Pendiente (esperando envío)</span>
            @elseif ($traspaso->estado == 'confirmado_origen')
                <span class="badge bg-info text-dark">En tránsito</span>
            @elseif ($traspaso->estado == 'confirmado_destino')
                <span class="badge bg-success">Recibido</span>
            @elseif ($traspaso->estado == 'rechazado')
                <span class="badge bg-danger">Rechazado en destino</span>
            @elseif ($traspaso->estado == 'anulado')
                <span class="badge bg-secondary">Anulado</span>
            @endif
        </h4>

        <a href="{{ route('traspasos.index') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-clock-history me-1"></i> Historial de Traspasos
        </a>
    </div>

    {{-- Resumen --}}
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-semibold text-muted mb-3">Sucursales</h6>
                    <p><span class="text-muted">Origen:</span> <span class="fw-semibold">{{ $traspaso->sucursalOrigen->nombre }}</span></p>
                    <p><span class="text-muted">Destino:</span> <span class="fw-semibold">{{ $traspaso->sucursalDestino->nombre }}</span></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-semibold text-muted mb-3">Detalle</h6>
                    <p><span class="text-muted">Fecha:</span> <span class="fw-semibold">{{ \Carbon\Carbon::parse($traspaso->fecha)->format('d/m/Y') }}</span></p>
                    <p><span class="text-muted">Observación:</span> <span class="fw-semibold">{{ $traspaso->observacion ?? 'Ninguna' }}</span></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Productos --}}
    <h6 class="fw-semibold mb-2"><i class="bi bi-box-seam text-warning me-1"></i> Productos enviados</h6>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light text-center">
                    <tr>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Cantidad</th>
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

    {{-- Acciones --}}
    <div class="d-flex justify-content-between align-items-center mt-4">
        <a href="{{ route('traspasos.pdf', $traspaso->id) }}" target="_blank" class="btn btn-outline-secondary">
            <i class="bi bi-printer me-1"></i> Imprimir Guía (PDF)
        </a>

        <div class="d-flex gap-2">
            @if ($traspaso->estado === 'pendiente')
                {{-- Editar --}}
                <a href="{{ route('traspasos.edit', $traspaso->id) }}" class="btn btn-warning">
                    ✏️ Editar traspaso
                </a>
                {{-- Confirmar envío --}}
                <form id="form-origen" method="POST" action="{{ route('traspasos.confirmarOrigen', $traspaso->id) }}">
                    @csrf
                </form>
                <button type="button" class="btn btn-success" onclick="confirmarOrigen()">✔️ Confirmar envío</button>
                {{-- Cancelar --}}
                <form id="form-anular" method="POST" action="{{ route('traspasos.anular', $traspaso->id) }}">
                    @csrf
                </form>
                <button type="button" class="btn btn-danger" onclick="anularTraspaso()">❌ Cancelar en origen</button>

            @elseif ($traspaso->estado === 'confirmado_origen')
                {{-- Confirmar en destino --}}
                <form id="form-destino" method="POST" action="{{ route('traspasos.confirmarDestino', $traspaso->id) }}">
                    @csrf
                </form>
                <button type="button" class="btn btn-success" onclick="confirmarDestino()">✔️ Confirmar recepción</button>

                {{-- Rechazar --}}
                <form id="form-rechazar" method="POST" action="{{ route('traspasos.rechazar', $traspaso->id) }}">
                    @csrf
                    <input type="hidden" name="motivo" id="motivo_rechazo">
                </form>
                <button type="button" class="btn btn-danger" onclick="rechazarTraspaso()">❌ Rechazar en destino</button>
            @endif

            <a href="{{ route('traspasos.index') }}" class="btn btn-outline-secondary">Volver</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmarOrigen() {
    Swal.fire({
        title: '¿Confirmar envío desde ORIGEN?',
        text: 'Esto descontará stock de la sucursal de origen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) document.getElementById('form-origen').submit();
    });
}

function confirmarDestino() {
    Swal.fire({
        title: '¿Confirmar recepción en DESTINO?',
        text: 'Esto sumará stock en la sucursal de destino.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) document.getElementById('form-destino').submit();
    });
}

function anularTraspaso() {
    Swal.fire({
        title: '¿Cancelar este traspaso en ORIGEN?',
        text: 'Esto eliminará el movimiento antes de enviarlo.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'Volver'
    }).then((result) => {
        if (result.isConfirmed) document.getElementById('form-anular').submit();
    });
}

function rechazarTraspaso() {
    Swal.fire({
        title: 'Rechazar traspaso',
        input: 'textarea',
        inputLabel: 'Indica el motivo del rechazo',
        inputPlaceholder: 'Escribe aquí...',
        showCancelButton: true,
        confirmButtonText: 'Rechazar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('motivo_rechazo').value = result.value || 'Rechazado en destino';
            document.getElementById('form-rechazar').submit();
        }
    });
}
</script>
@endpush
