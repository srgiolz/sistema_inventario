@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">📦 Historial de Salidas</h2>

    <a href="{{ route('salidas.create') }}" class="btn btn-primary mb-4">+ Nueva Salida</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @forelse($salidas as $salida)
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light fw-bold">
                🧾 Salida #{{ $salida->id }} — {{ \Carbon\Carbon::parse($salida->fecha)->format('d/m/Y') }}
            </div>
            <div class="card-body">
                <p><strong>Sucursal:</strong> {{ $salida->sucursal->nombre ?? '—' }}</p>
                <p><strong>Tipo:</strong> {{ $salida->tipo }}</p>
                <p><strong>Motivo:</strong> {{ $salida->motivo }}</p>
                <p><strong>Observación:</strong> {{ $salida->observacion ?? '—' }}</p>

                <div class="table-responsive mt-3">
                    <table class="table table-sm table-bordered align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salida->detalles as $index => $detalle)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="text-start">
                                        {{ $detalle->producto->item_codigo }} - {{ $detalle->producto->descripcion }}
                                    </td>
                                    <td>{{ $detalle->cantidad }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-info">No hay salidas registradas.</div>
    @endforelse
</div>
@endsection
