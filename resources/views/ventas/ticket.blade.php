<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket de Venta</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
        }
        .titulo {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .seccion {
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #333;
            padding: 6px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        .totales {
            margin-top: 15px;
        }
    </style>
</head>
<body>

    <div class="titulo">Ticket de Venta Interno</div>

    <div class="seccion">
        <strong>Cliente:</strong> {{ $venta->cliente->nombre }} {{ $venta->cliente->apellido }}<br>
        <strong>Sucursal:</strong> {{ $venta->sucursal->nombre }}<br>
        <strong>Fecha:</strong> {{ $venta->fecha->format('d/m/Y H:i') }}<br>
        <strong>Pago:</strong> {{ ucfirst($venta->tipoPago->nombre) }}<br> <!-- Cambiado de tipo_pago a tipoPago->nombre -->
        <strong>Factura:</strong>
        @if($venta->con_factura)
            <span style="color: green;">Con factura</span>
        @else
            <span style="color: gray;">Sin factura</span>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cant</th>
                <th>P/U</th>
                <th>Desc.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $detalle)
            <tr>
                <td>{{ $detalle->producto->descripcion ?? 'Producto eliminado' }}</td>
                <td>{{ $detalle->cantidad }}</td>
                <td>Bs {{ number_format($detalle->precio_unitario, 2) }}</td>
                <td>Bs {{ number_format($detalle->descuento, 2) }}</td>
                <td>Bs {{ number_format($detalle->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totales">
        <p><strong>Descuento General:</strong> Bs {{ number_format($venta->descuento_total, 2) }}</p>
        <p><strong>Total:</strong> Bs {{ number_format($venta->total, 2) }}</p>
    </div>

</body>
</html>

