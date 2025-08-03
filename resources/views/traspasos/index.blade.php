@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Historial de Traspasos</h2>

    <a href="{{ route('traspasos.create') }}" class="btn btn-primary mb-3">+ Nuevo Traspaso</a>

    <table class="table table-bordered table-hover table-sm align-middle">
        <thead class="table-dark text-center">
            <tr>
                <th>ID</th>
                <th>Tipo</th>
                <th>De</th>
                <th>A</th>
                <th>Fecha</th>
                <th>Observación</th>
                <th>Productos</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($traspasos as $t)
                <tr>
                    <td class="text-center">{{ $t->id }}</td>
                    <td class="text-center">
                        @if($t->tipo == 'abastecimiento')
                            <span class="badge bg-success">Abastecimiento</span>
                        @else
                            <span class="badge bg-secondary">Sucursal</span>
                        @endif
                    </td>
                    <td>{{ $t->sucursalOrigen->nombre }}</td>
                    <td>{{ $t->sucursalDestino->nombre }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($t->fecha)->format('d/m/Y') }}</td>
                    <td>{{ $t->observacion }}</td>
                    <td>
                        <ul class="mb-0">
                            @foreach($t->detalles as $detalle)
                                <li>
                                    {{ $detalle->producto->item_codigo }} - {{ $detalle->producto->descripcion }}
                                    <span class="text-muted">({{ $detalle->cantidad }})</span>
                                </li>
                            @endforeach
                        </ul>
                    </td>
                    <td class="text-center">
                        @if($t->estado == 'pendiente')
                            <span class="badge bg-warning text-dark">Pendiente</span>
                        @elseif($t->estado == 'confirmado')
                            <span class="badge bg-success">Confirmado</span>
                        @else
                            <span class="badge bg-danger">Rechazado</span>
                        @endif
                    </td>
                    <td class="text-center">
    <a href="{{ url('/traspasos/' . $t->id . '/revisar') }}" class="btn btn-outline-primary btn-sm" title="Revisar">
        <i class="bi bi-eye"></i> Revisar
    </a>

    @if($t->estado == 'pendiente')
        <a href="{{ route('traspasos.edit', $t->id) }}" class="btn btn-outline-warning btn-sm mt-1" title="Editar">
            <i class="bi bi-pencil"></i> Editar
        </a>
    @endif
</td>

                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
