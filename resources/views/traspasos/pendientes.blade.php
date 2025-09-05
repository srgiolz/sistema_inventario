@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">
        <i class="bi bi-hourglass-split text-warning"></i>
        Traspasos Pendientes
    </h4>

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

    @if($traspasos->isEmpty())
        <div class="alert alert-info shadow-sm">
            📭 No hay traspasos pendientes por revisar.
        </div>
    @else
        <table class="table table-bordered table-hover shadow-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Origen</th>
                    <th>Destino</th>
                    <th>Fecha</th>
                    <th>Observación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($traspasos as $t)
                    <tr>
                        <td class="fw-bold">{{ $t->id }}</td>
                        <td>{{ $t->sucursalOrigen->nombre }}</td>
                        <td>{{ $t->sucursalDestino->nombre }}</td>
                        <td>{{ \Carbon\Carbon::parse($t->fecha)->format('d/m/Y') }}</td>
                        <td>{{ $t->observacion ?? '-' }}</td>
                        <td>
                            {{-- Revisar siempre disponible --}}
                            <a href="{{ route('traspasos.revisar', $t->id) }}" class="btn btn-sm btn-primary">
                                🔍 Revisar
                            </a>

                            {{-- Confirmar envío (origen) --}}
                            <a href="{{ route('traspasos.confirmarOrigen', $t->id) }}" class="btn btn-sm btn-success"
                               onclick="return confirm('¿Confirmar envío desde ORIGEN? Esto descontará stock en la sucursal de origen.')">
                                ✅ Confirmar envío
                            </a>

                            {{-- Cancelar en origen --}}
                            <a href="{{ route('traspasos.anular', $t->id) }}" class="btn btn-sm btn-danger"
                               onclick="return confirm('¿Cancelar este traspaso en ORIGEN? Se eliminará antes de enviarlo.')">
                                ❌ Cancelar en origen
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
