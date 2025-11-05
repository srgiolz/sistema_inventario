@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 1100px;">

    {{-- 🔹 Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-box-arrow-up text-danger"></i>
            <span class="fw-semibold">Revisión de la Salida #{{ $salida->id }}</span>

            @if ($salida->estado == 'pendiente')
                <span class="badge bg-warning text-dark">Pendiente</span>
            @elseif ($salida->estado == 'confirmado')
                <span class="badge bg-success">Confirmada</span>
            @elseif ($salida->estado == 'anulado')
                <span class="badge bg-secondary">Anulada</span>
            @endif
        </h4>

        <a href="{{ route('salidas.index') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-clock-history me-1"></i> Historial de Salidas
        </a>
    </div>

    {{-- 🧾 Información general --}}
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-semibold text-muted mb-3">Sucursal y Datos</h6>
                    <p><span class="text-muted">Sucursal:</span> <span class="fw-semibold">{{ $salida->sucursal->nombre }}</span></p>
                    <p><span class="text-muted">Fecha:</span> <span class="fw-semibold">{{ \Carbon\Carbon::parse($salida->fecha)->format('d/m/Y') }}</span></p>
                    <p><span class="text-muted">Tipo:</span> <span class="fw-semibold">{{ $salida->tipo ?? 'No especificado' }}</span></p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-semibold text-muted mb-3">Detalle y Observaciones</h6>
                    <p><span class="text-muted">Motivo:</span> <span class="fw-semibold">{{ $salida->motivo ?? 'Sin motivo registrado' }}</span></p>
                    <p><span class="text-muted">Observación:</span> <span class="fw-semibold">{{ $salida->observacion ?? 'Ninguna' }}</span></p>
                    @if($salida->estado === 'anulado' && $salida->motivo_anulacion)
                        <p><span class="text-muted">Motivo de anulación:</span> 
                        <span class="fw-semibold text-danger">{{ $salida->motivo_anulacion }}</span></p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 📦 Productos --}}
    <h6 class="fw-semibold mb-2">
        <i class="bi bi-box-seam text-danger me-1"></i> Productos retirados
    </h6>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-0">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-danger text-center">
                    <tr>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th width="100">Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($salida->detalles as $detalle)
                        <tr>
                            <td>{{ $detalle->producto->codigo_item }}</td>
                            <td style="white-space: normal; word-break: break-word;">{{ $detalle->producto->descripcion }}</td>
                            <td class="text-center fw-semibold">{{ $detalle->cantidad }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- 🎛️ Acciones --}}
    <div class="d-flex justify-content-between align-items-center mt-4">
        <a href="{{ route('salidas.pdf', $salida->id) }}" target="_blank" class="btn btn-outline-secondary btn-lg d-flex align-items-center gap-2 shadow-sm">
            <i class="bi bi-printer"></i> Imprimir Guía (PDF)
        </a>

        <div class="d-flex gap-3 flex-wrap justify-content-end">
            @if ($salida->estado === 'pendiente')
                {{-- ✏️ Editar --}}
                <a href="{{ route('salidas.edit', $salida->id) }}" class="btn btn-warning btn-lg d-flex align-items-center gap-2 shadow-sm">
                    <i class="bi bi-pencil-square"></i> Editar
                </a>

                {{-- ✅ Confirmar --}}
                <form id="form-confirmar" method="POST" action="{{ route('salidas.confirm', $salida->id) }}">
                    @csrf
                </form>
                <button type="button" class="btn btn-success btn-lg d-flex align-items-center gap-2 shadow-sm" onclick="confirmarSalida()">
                    <i class="bi bi-check-circle"></i> Confirmar salida
                </button>

                {{-- ❌ Cancelar --}}
                <form id="form-anular-pendiente" method="POST" action="{{ route('salidas.anular', $salida->id) }}">
                    @csrf
                    <input type="hidden" name="motivo_anulacion" id="motivo_pendiente">
                </form>
                <button type="button" class="btn btn-danger btn-lg d-flex align-items-center gap-2 shadow-sm" onclick="cancelarSalida()">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>

            @elseif ($salida->estado === 'confirmado')
                {{-- ❌ Anular salida confirmada --}}
                <form id="form-anular" method="POST" action="{{ route('salidas.anular', $salida->id) }}">
                    @csrf
                    <input type="hidden" name="motivo_anulacion" id="motivo_confirmada">
                </form>
                <button type="button" class="btn btn-danger btn-lg d-flex align-items-center gap-2 shadow-sm" onclick="anularSalida()">
                    <i class="bi bi-x-circle"></i> Anular salida
                </button>
            @endif

            {{-- ⬅️ Volver --}}
            <a href="{{ route('salidas.index') }}" class="btn btn-outline-secondary btn-lg d-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .btn-lg {
        padding: 0.55rem 1.2rem !important;
        font-size: 0.95rem !important;
        border-radius: 0.45rem !important;
        transition: all 0.2s ease-in-out;
    }
    .btn-lg i { font-size: 1.1rem; }
    .btn-lg:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }
    .btn-warning {
        color: #333 !important;
        background-color: #ffc107 !important;
        border-color: #e0a800 !important;
    }
    .btn-warning:hover {
        background-color: #e0a800 !important;
        color: #fff !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- 🔔 Alertas de acción --}}
<script>
function confirmarSalida() {
    Swal.fire({
        title: '¿Confirmar salida?',
        text: 'Esto descontará el stock de la sucursal seleccionada.',
        icon: 'question',
        iconColor: '#198754',
        showCancelButton: true,
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        customClass: {
            popup: 'rounded-4 shadow-sm',
            confirmButton: 'px-4 py-2 fw-semibold',
            cancelButton: 'px-4 py-2 fw-semibold'
        }
    }).then(result => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Salida confirmada',
                text: 'El stock fue actualizado correctamente.',
                icon: 'success',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
                willClose: () => {
                    document.getElementById('form-confirmar').submit();
                }
            });
        }
    });
}

function cancelarSalida() {
    Swal.fire({
        title: 'Cancelar salida',
        input: 'textarea',
        inputLabel: 'Especifica el motivo de la cancelación:',
        inputPlaceholder: 'Motivo...',
        inputAttributes: { 'aria-label': 'Motivo de cancelación' },
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Cancelar salida',
        cancelButtonText: 'Volver',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        customClass: {
            popup: 'rounded-4 shadow-sm',
            input: 'form-control',
            confirmButton: 'px-4 py-2 fw-semibold',
            cancelButton: 'px-4 py-2 fw-semibold'
        }
    }).then(result => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Salida cancelada',
                text: 'La salida fue anulada correctamente.',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
                willClose: () => {
                    document.getElementById('motivo_pendiente').value = result.value || 'Cancelada por el usuario';
                    document.getElementById('form-anular-pendiente').submit();
                }
            });
        }
    });
}

function anularSalida() {
    Swal.fire({
        title: 'Anular salida confirmada',
        input: 'textarea',
        inputLabel: 'Indica el motivo de anulación:',
        inputPlaceholder: 'Motivo...',
        icon: 'error',
        showCancelButton: true,
        confirmButtonText: 'Anular salida',
        cancelButtonText: 'Volver',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-4 shadow-sm',
            input: 'form-control',
            confirmButton: 'px-4 py-2 fw-semibold',
            cancelButton: 'px-4 py-2 fw-semibold'
        }
    }).then(result => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Salida anulada',
                text: 'El stock fue restaurado correctamente.',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
                willClose: () => {
                    document.getElementById('motivo_confirmada').value = result.value || 'Anulada por el usuario';
                    document.getElementById('form-anular').submit();
                }
            });
        }
    });
}

// ✅ Mensaje automático de éxito (desde controlador)
@if(session('success'))
Swal.fire({
    icon: 'success',
    title: '¡Éxito!',
    text: '{{ session('success') }}',
    timer: 1800,
    showConfirmButton: false,
    timerProgressBar: true,
    customClass: { popup: 'rounded-4 shadow-sm' }
});
@endif
</script>
@endpush
