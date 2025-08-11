@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Detalle de Venta #{{ $venta->id }}</h2>

    <p><strong>Cliente:</strong> {{ $venta->cliente->nombre }} {{ $venta->cliente->apellido }}</p>
    <p><strong>Sucursal:</strong> {{ $venta->sucursal->nombre }}</p>
    <p><strong>Fecha:</strong> {{ $venta->fecha }}</p>
    <p><strong>Tipo de Pago:</strong> {{ ucfirst($venta->tipoPago->nombre) }}</p> <!-- Cambiado de tipo_pago a tipoPago->nombre -->
    <p><strong>Factura:</strong>
    @if($venta->con_factura)
        <span class="badge bg-success">Con factura</span>
    @else
        <span class="badge bg-secondary">Sin factura</span>
    @endif
    </p>
    <p><strong>Descuento General:</strong> Bs {{ number_format($venta->descuento_total, 2) }}</p>
    <p><strong>Total Final:</strong> Bs {{ number_format($venta->total, 2) }}</p>

    <h4>Productos Vendidos</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Descuento</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->producto->descripcion }}</td>
                    <td>{{ $detalle->cantidad }}</td>
                    <td>Bs {{ number_format($detalle->precio_unitario, 2) }}</td>
                    <td>Bs {{ number_format($detalle->descuento, 2) }}</td>
                    <td>Bs {{ number_format($detalle->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('ventas.index') }}" class="btn btn-secondary">Volver</a>
</div>
@endsection
