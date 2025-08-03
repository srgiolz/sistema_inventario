<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            max-height: 60px;
            margin-bottom: 10px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            margin-top: 10px;
        }

        .info {
            margin-bottom: 10px;
        }

        .info strong {
            display: inline-block;
            width: 100px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #aaa;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
        }

        .footer {
            margin-top: 40px;
            font-size: 10px;
            text-align: center;
            color: #777;
        }
    </style>
</head>
<body>

    <div class="header">
        <img src="{{ public_path('images/yatinasoft_log.png') }}" class="logo">
        <div class="title">Comprobante de Entrada #{{ $entrada->id }}</div>
    </div>

    <div class="info">
        <p><strong>Sucursal:</strong> {{ $entrada->sucursal->nombre }}</p>
        <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($entrada->fecha)->format('d/m/Y') }}</p>
        <p><strong>Tipo:</strong> {{ $entrada->tipo ?? 'No especificado' }}</p>
        @if($entrada->observacion)
            <p><strong>Observación:</strong> {{ $entrada->observacion }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entrada->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->producto->item_codigo }} - {{ $detalle->producto->descripcion }}</td>
                    <td>{{ $detalle->cantidad }}</td>
                    <td>{{ number_format($detalle->precio_unitario, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Sistema de Inventario - Documento generado automáticamente el {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>
