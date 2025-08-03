@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Historial de Ventas</h2>
    <a href="{{ route('ventas.create') }}" class="btn btn-primary mb-3">
    <i class="bi bi-plus-circle"></i> Nueva Venta
</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Sucursal</th>
                <th>Factura</th>
                <th>Fecha</th>
                <th>Pago</th>
                <th>Desc. General</th>
                <th>Total</th>
                <th>Ver Detalles</th>
            </tr>
        </thead>
        <tbody>
    @foreach($ventas as $venta)
        <tr>
            <td>{{ $venta->id }}</td>
            <td>{{ $venta->cliente->nombre }} {{ $venta->cliente->apellido }}</td>
            <td>{{ $venta->sucursal->nombre }}</td>
            <td>
                @if($venta->con_factura)
                    <span class="badge bg-success">Con factura</span>
                @else
                    <span class="badge bg-secondary">Sin factura</span>
                @endif
            </td>
            <td>{{ $venta->fecha }}</td>
            <td>{{ ucfirst($venta->tipo_pago) }}</td>
            <td>Bs {{ number_format($venta->descuento_total, 2) }}</td>
            <td>Bs {{ number_format($venta->total, 2) }}</td>
            <td>
                <a href="{{ route('ventas.show', $venta->id) }}" class="btn btn-info btn-sm">Detalles</a>
                <a href="{{ route('ventas.ticket', $venta->id) }}" class="btn btn-success btn-sm" target="_blank">Ticket</a>
            </td>
        </tr>
    @endforeach
</tbody>

    </table>
</div>
@endsection