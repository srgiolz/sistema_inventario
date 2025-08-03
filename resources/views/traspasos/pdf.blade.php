<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Traspaso #{{ $traspaso->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
        }
        .titulo {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .seccion {
            margin-bottom: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #333;
            padding: 5px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .firmas {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .firma {
            width: 45%;
            text-align: center;
        }
        .firma hr {
            margin: 40px 0 5px;
            border: none;
            border-top: 1px solid #000;
        }
    </style>
</head>
<body>

    <div class="titulo">Guía de Traspaso Interno</div>

    <div class="seccion">
        <strong>N° Traspaso:</strong> {{ $traspaso->id }}<br>
        <strong>Tipo:</strong> {{ ucfirst($traspaso->tipo) }}<br>
        <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($traspaso->fecha)->format('d/m/Y') }}<br>
        <strong>De:</strong> {{ $traspaso->sucursalOrigen->nombre }}<br>
        <strong>Para:</strong> {{ $traspaso->sucursalDestino->nombre }}<br>
        @if($traspaso->observacion)
            <strong>Observación:</strong> {{ $traspaso->observacion }}<br>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Código</th>
                <th>Descripción</th>
                <th>Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($traspaso->detalles as $i => $detalle)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $detalle->producto->item_codigo }}</td>
                <td>{{ $detalle->producto->descripcion }}</td>
                <td style="text-align: center;">{{ $detalle->cantidad }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="firmas">
        <div class="firma">
            <hr>
            <p>Entregado por</p>
        </div>
        <div class="firma">
            <hr>
            <p>Recibido por</p>
        </div>
    </div>

</body>
</html>
