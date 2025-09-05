<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Guía de Traspaso #{{ $traspaso->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h2, h4 { margin: 0; padding: 0; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        table th, table td { border: 1px solid #ddd; padding: 6px; }
        table th { background: #f5f5f5; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .estado { font-size: 13px; padding: 5px 10px; border-radius: 4px; }
        .pendiente { background: #ffeeba; color: #856404; }
        .transito { background: #bee5eb; color: #0c5460; }
        .confirmado { background: #c3e6cb; color: #155724; }
        .anulado { background: #f5c6cb; color: #721c24; }
    </style>
</head>
<body>
    <table width="100%">
        <tr>
            <td>
                <h2>📦 Guía de Traspaso</h2>
                <h4>#{{ $traspaso->id }}</h4>
            </td>
            <td class="text-right">
                <div class="estado
                    @if($traspaso->estado == 'pendiente') pendiente
                    @elseif($traspaso->estado == 'confirmado_origen') transito
                    @elseif($traspaso->estado == 'confirmado_destino') confirmado
                    @elseif($traspaso->estado == 'anulado') anulado
                    @endif">
                    @if($traspaso->estado == 'pendiente') Pendiente
                    @elseif($traspaso->estado == 'confirmado_origen') En tránsito
                    @elseif($traspaso->estado == 'confirmado_destino') Confirmado en destino
                    @elseif($traspaso->estado == 'anulado') Anulado
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <hr>

    {{-- Información general --}}
    <table>
        <tr>
            <th width="25%">Sucursal Origen</th>
            <td>{{ $traspaso->sucursalOrigen->nombre }}</td>
            <th width="25%">Sucursal Destino</th>
            <td>{{ $traspaso->sucursalDestino->nombre }}</td>
        </tr>
        <tr>
            <th>Fecha</th>
            <td>{{ \Carbon\Carbon::parse($traspaso->fecha)->format('d/m/Y') }}</td>
            <th>Tipo</th>
            <td>{{ ucfirst($traspaso->tipo) }}</td>
        </tr>
        <tr>
            <th>Observación</th>
            <td colspan="3">{{ $traspaso->observacion ?? 'Ninguna' }}</td>
        </tr>
        @if($traspaso->estado == 'anulado' && $traspaso->motivo_anulacion)
        <tr>
            <th>Motivo anulación</th>
            <td colspan="3">{{ $traspaso->motivo_anulacion }}</td>
        </tr>
        @endif
    </table>

    {{-- Productos --}}
    <h4 style="margin-top:20px;">📝 Productos del Traspaso</h4>
    <table>
        <thead>
            <tr class="text-center">
                <th style="width:15%">Código</th>
                <th>Descripción</th>
                <th style="width:15%">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach($traspaso->detalles as $detalle)
                <tr>
                    <td class="text-center">{{ $detalle->producto->codigo_item }}</td>
                    <td>{{ $detalle->producto->descripcion }}</td>
                    <td class="text-center fw-bold">{{ $detalle->cantidad }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Firmas --}}
    <table style="margin-top:40px;">
        <tr>
            <td class="text-center" style="width:50%">
                ___________________________<br>
                <small><b>Entrega (Origen)</b></small>
            </td>
            <td class="text-center" style="width:50%">
                ___________________________<br>
                <small><b>Recepción (Destino)</b></small>
            </td>
        </tr>
    </table>
</body>
</html>
