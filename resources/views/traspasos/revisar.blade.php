@extends('layouts.app')

@section('content')
<div class="container">
    <h4>🧾 Revisión del Traspaso #{{ $traspaso->id }}</h4>

    <div class="mb-3">
        <strong>Origen:</strong> {{ $traspaso->sucursalOrigen->nombre }} <br>
        <strong>Destino:</strong> {{ $traspaso->sucursalDestino->nombre }} <br>
        <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($traspaso->fecha)->format('d/m/Y') }} <br>
        <strong>Observación:</strong> {{ $traspaso->observacion ?? 'Ninguna' }} <br>
        <strong>Estado:</strong>
        @if ($traspaso->estado == 'pendiente')
            <span class="badge bg-warning text-dark">Pendiente</span>
        @elseif ($traspaso->estado == 'confirmado')
            <span class="badge bg-success">Confirmado</span>
        @else
            <span class="badge bg-danger">Rechazado</span>
        @endif
    </div>

    <hr>

    <h5>📦 Productos enviados</h5>
    <table class="table table-bordered table-sm">
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
                    <td>{{ $detalle->producto->item_codigo }}</td>
                    <td>{{ $detalle->producto->descripcion }}</td>
                    <td class="text-center">{{ $detalle->cantidad }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        <a href="{{ route('traspasos.pdf', $traspaso->id) }}" target="_blank" class="btn btn-outline-secondary">
            <i class="bi bi-printer"></i> Imprimir Guía (PDF)
        </a>

        @if($traspaso->estado == 'pendiente')
            <!-- Botones con SweetAlert -->
            <button type="button" class="btn btn-success" onclick="confirmarTraspaso()">✅ Confirmar recepción</button>
            <button type="button" class="btn btn-danger ms-2" onclick="rechazarTraspaso()">❌ Rechazar</button>

            <!-- Formularios ocultos -->
            <form id="form-confirmar" method="POST" action="{{ route('traspasos.confirmar', $traspaso->id) }}">
                @csrf
                @method('PATCH')
            </form>

            <form id="form-rechazar" method="POST" action="{{ route('traspasos.rechazar', $traspaso->id) }}">
                @csrf
                @method('PATCH')
            </form>
        @endif

        <a href="{{ route('traspasos.index') }}" class="btn btn-secondary float-end">Volver</a>
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
            if (result.isConfirmed) {
                document.getElementById('form-confirmar').submit();
            }
        });
    }

    function rechazarTraspaso() {
        Swal.fire({
            title: '¿Rechazar traspaso?',
            text: 'Esto revertirá el stock a la sucursal origen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, rechazar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-rechazar').submit();
            }
        });
    }
</script>
@endpush

