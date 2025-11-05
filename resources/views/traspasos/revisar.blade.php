@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 1100px;">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-arrow-left-right text-success"></i>
            <span class="fw-semibold">Revisión del Traspaso #{{ $traspaso->id }}</span>

            @php
                $estado = [
                    'pendiente' => ['text' => 'Pendiente (esperando envío)', 'class' => 'bg-warning text-dark'],
                    'confirmado_origen' => ['text' => 'En tránsito', 'class' => 'bg-info text-dark'],
                    'confirmado_destino' => ['text' => 'Recibido', 'class' => 'bg-success'],
                    'rechazado' => ['text' => 'Rechazado', 'class' => 'bg-danger'],
                    'anulado' => ['text' => 'Anulado', 'class' => 'bg-secondary']
                ];
            @endphp

            <span class="badge {{ $estado[$traspaso->estado]['class'] }} px-3 py-2">
                {{ $estado[$traspaso->estado]['text'] }}
            </span>
        </h4>

        <a href="{{ route('traspasos.index') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-clock-history me-1"></i> Historial de Traspasos
        </a>
    </div>

    {{-- Resumen --}}
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-shadow">
                <div class="card-body">
                    <h6 class="fw-semibold text-muted mb-3">Sucursales</h6>
                    <p><span class="text-muted">Origen:</span> <span class="fw-semibold">{{ $traspaso->sucursalOrigen->nombre }}</span></p>
                    <p><span class="text-muted">Destino:</span> <span class="fw-semibold">{{ $traspaso->sucursalDestino->nombre }}</span></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 hover-shadow">
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
    <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">
        <a href="{{ route('traspasos.pdf', $traspaso->id) }}" target="_blank" class="btn btn-outline-dark">
            <i class="bi bi-printer me-1"></i> Imprimir Guía (PDF)
        </a>

        <div class="d-flex flex-wrap gap-2 justify-content-end">
            @if ($traspaso->estado === 'pendiente')
                <a href="{{ route('traspasos.edit', $traspaso->id) }}" class="btn btn-warning">
                    <i class="bi bi-pencil-square me-1"></i> Editar
                </a>
                <form id="form-origen" method="POST" action="{{ route('traspasos.confirmarOrigen', $traspaso->id) }}">@csrf</form>
                <button type="button" class="btn btn-success" onclick="accionTraspaso('origen')">
                    <i class="bi bi-send-check me-1"></i> Confirmar envío
                </button>
                <form id="form-rechazar-origen" method="POST" action="{{ route('traspasos.rechazar', $traspaso->id) }}">@csrf
                    <input type="hidden" name="motivo" id="motivo_rechazo_origen">
                </form>
                <button type="button" class="btn btn-danger" onclick="accionTraspaso('cancelar')">
                    <i class="bi bi-x-circle me-1"></i> Cancelar en origen
                </button>
            @elseif ($traspaso->estado === 'confirmado_origen')
                <form id="form-destino" method="POST" action="{{ route('traspasos.confirmarDestino', $traspaso->id) }}">@csrf</form>
                <button type="button" class="btn btn-success" onclick="accionTraspaso('destino')">
                    <i class="bi bi-check2-circle me-1"></i> Confirmar recepción
                </button>
                <form id="form-rechazar" method="POST" action="{{ route('traspasos.rechazar', $traspaso->id) }}">@csrf
                    <input type="hidden" name="motivo" id="motivo_rechazo">
                </form>
                <button type="button" class="btn btn-danger" onclick="accionTraspaso('rechazar')">
                    <i class="bi bi-exclamation-octagon me-1"></i> Rechazar en destino
                </button>
            @endif
            <a href="{{ route('traspasos.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>
</div>

@push('styles')
<style>
    .hover-shadow:hover { box-shadow: 0 0 10px rgba(0,0,0,0.1); transition: .3s; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function accionTraspaso(tipo) {
    let titulo, texto, icono, formId, exito;

    switch(tipo) {
        case 'origen':
            titulo = '¿Confirmar envío desde origen?';
            texto = 'Esto descontará stock de la sucursal de origen.';
            icono = 'question';
            formId = 'form-origen';
            exito = 'El traspaso fue enviado correctamente desde la sucursal de origen.';
            break;

        case 'destino':
            titulo = '¿Confirmar recepción en destino?';
            texto = 'Esto sumará stock en la sucursal de destino.';
            icono = 'question';
            formId = 'form-destino';
            exito = 'El traspaso fue recibido correctamente en la sucursal de destino.';
            break;

        case 'cancelar':
            titulo = '¿Cancelar traspaso en origen?';
            texto = 'El traspaso se marcará como cancelado sin afectar stock.';
            icono = 'warning';
            formId = 'form-rechazar-origen';
            document.getElementById('motivo_rechazo_origen').value = 'Cancelado en origen';
            exito = 'El traspaso fue cancelado antes del envío.';
            break;

        case 'rechazar':
            Swal.fire({
                title: 'Rechazar traspaso',
                input: 'textarea',
                inputLabel: 'Indica el motivo del rechazo',
                inputPlaceholder: 'Escribe aquí...',
                showCancelButton: true,
                confirmButtonText: 'Rechazar',
                cancelButtonText: 'Cancelar',
                icon: 'warning'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('motivo_rechazo').value = result.value || 'Rechazado en destino';
                    document.getElementById('form-rechazar').submit();
                    Swal.fire({
                        icon: 'success',
                        title: 'Traspaso rechazado',
                        text: 'El traspaso fue rechazado correctamente.',
                        showConfirmButton: false,
                        timer: 1500,
                        willClose: () => window.location.href = "{{ route('traspasos.index') }}"
                    });
                }
            });
            return;
    }

    Swal.fire({
        title: titulo,
        text: texto,
        icon: icono,
        showCancelButton: true,
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
            Swal.fire({
                icon: 'success',
                title: 'Operación exitosa',
                text: exito,
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
                willClose: () => window.location.href = "{{ route('traspasos.index') }}"
            });
        }
    });
}
</script>
@endpush
@endsection
