@extends('layouts.app')

@section('content')
<div class="container">
    <h4>📦 Detalles del Traspaso #{{ $traspaso->id }}</h4>

    <div class="mb-3">
        <strong>Tipo:</strong> {{ ucfirst($traspaso->tipo) }}<br>
        <strong>Estado:</strong>
        @if ($traspaso->estado == 'pendiente')
            <span class="badge bg-warning text-dark">Pendiente</span>
        @elseif ($traspaso->estado == 'confirmado')
            <span class="badge bg-success">Confirmado</span>
        @else
            <span class="badge bg-danger">Rechazado</span>
        @endif
        <br>
        <strong>Origen:</strong> {{ $traspaso->sucursalOrigen->nombre }}<br>
        <strong>Destino:</strong> {{ $traspaso->sucursalDestino->nombre }}<br>
        <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($traspaso->fecha)->format('d/m/Y') }}<br>
        <strong>Observación:</strong> {{ $traspaso->observacion ?? 'Sin observación' }}
    </div>

    <h5>🧾 Productos transferidos</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Código</th>
                <th>Descripción</th>
                <th>Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($traspaso->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->producto->codigo_item }}</td>
                    <td>{{ $detalle->producto->descripcion }}</td>
                    <td>{{ $detalle->cantidad }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('traspasos.index') }}" class="btn btn-secondary">🔙 Volver al historial</a>
</div>
@endsection
