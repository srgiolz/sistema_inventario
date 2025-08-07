<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Guía de Traspaso #{{ $traspaso->id }}</title>
    <style>
        /* ===== Página compacta (media hoja aprox.) ===== */
        @page { margin: 12mm 10mm; } /* márgenes pequeños */
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1f2937; line-height: 1.25; }

        .row { display:flex; justify-content:space-between; align-items:flex-start; gap:10px; }
        .muted { color:#6b7280; }

        /* Encabezado compacto */
        .header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 6mm; }
        .brand { font-size: 14px; font-weight: 700; letter-spacing: .2px; margin-bottom: 2px; }
        .title { font-size: 13px; font-weight: 700; }
        .meta { text-align:right; font-size: 10.5px; }
        .meta .rowline { margin: 1px 0; }
        .pill { display:inline-block; padding: 0 6px; border-radius: 999px; font-size: 10px; line-height: 1.6; vertical-align: middle; }
        .pill-pendiente { background:#fef3c7; color:#92400e; }
        .pill-confirmado { background:#dcfce7; color:#166534; }
        .pill-rechazado { background:#fee2e2; color:#991b1b; }

        /* Tarjetas y layout apretado */
        .section { margin-bottom: 6mm; }
        .card { border:1px solid #e5e7eb; border-radius: 6px; padding: 6px 8px; }
        .card h4 { margin: 0 0 4px; font-size: 11.5px; color:#374151; }
        .grid2 { display:grid; grid-template-columns: 1fr 1fr; gap: 6px 10px; }
        .label { color:#6b7280; }
        .value { font-weight: 600; }

        /* Tabla muy compacta */
        table { width:100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; }
        thead th { background:#f3f4f6; color:#374151; font-weight: 700; font-size: 11px; }
        tbody tr:nth-child(even) { background:#fafafa; }
        td.qty, th.qty { text-align:center; width: 70px; }
        td.code { white-space: nowrap; width: 120px; font-family: monospace; font-size: 10.5px; }
        .tight { margin-top: 2mm; }

        /* Totales y nota */
        .totals { margin-top: 3px; text-align: right; font-size: 10.5px; }
        .note { margin-top: 2px; color:#6b7280; font-size: 10px; }

        /* Firmas compactas */
        .sign-row { display:flex; justify-content:space-between; gap: 16px; margin-top: 8mm; }
        .sign { flex:1; text-align:center; }
        .sign .line { margin: 16mm 0 4px; border:none; border-top:1px solid #111827; }
        .sign .who { font-weight: 600; font-size: 10.5px; }
        .sign .hint { color:#6b7280; font-size: 10px; }

        /* Footer chico (opcional) */
        .footer { position: fixed; bottom: 8mm; left: 10mm; right: 10mm; display:flex; justify-content:space-between; color:#6b7280; font-size: 9.5px; }
    </style>
</head>
<body>

    {{-- Encabezado --}}
    <div class="header">
        <div>
            <div class="brand">YatiñaSoft</div>
            <div class="title">Guía de Traspaso Interno</div>
        </div>
        <div class="meta">
            <div class="rowline"><strong>N°:</strong> #{{ $traspaso->id }}</div>
            <div class="rowline"><strong>Tipo:</strong> {{ ucfirst($traspaso->tipo) }}</div>
            <div class="rowline">
                <strong>Estado:</strong>
                @php($estado = $traspaso->estado)
                @if($estado === 'pendiente')
                    <span class="pill pill-pendiente">Pendiente</span>
                @elseif($estado === 'confirmado')
                    <span class="pill pill-confirmado">Confirmado</span>
                @else
                    <span class="pill pill-rechazado">Rechazado</span>
                @endif
            </div>
            <div class="rowline"><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($traspaso->fecha)->format('d/m/Y') }}</div>
        </div>
    </div>

    {{-- Resumen (apretado) --}}
    <div class="section">
        <div class="grid2">
            <div class="card">
                <h4>Sucursales</h4>
                <div><span class="label">Origen:</span> <span class="value">{{ $traspaso->sucursalOrigen->nombre }}</span></div>
                <div><span class="label">Destino:</span> <span class="value">{{ $traspaso->sucursalDestino->nombre }}</span></div>
            </div>
            <div class="card">
                <h4>Detalle</h4>
                <div><span class="label">Observación:</span> <span class="value">{{ $traspaso->observacion ?? 'Ninguna' }}</span></div>
                @if($traspaso->fecha_confirmacion)
                    <div><span class="label">Confirmado el:</span> <span class="value">{{ \Carbon\Carbon::parse($traspaso->fecha_confirmacion)->format('d/m/Y H:i') }}</span></div>
                @endif
            </div>
        </div>
    </div>

    {{-- Tabla productos (compacta) --}}
    <table class="tight">
        <thead>
            <tr>
                <th style="width:36px">#</th>
                <th style="width:120px">Código</th>
                <th>Descripción</th>
                <th class="qty">Cantidad</th>
            </tr>
        </thead>
        <tbody>
        @php($totalItems = 0)
        @foreach ($traspaso->detalles as $i => $detalle)
            @php($totalItems += (int)$detalle->cantidad)
            <tr>
                <td class="qty">{{ $i + 1 }}</td>
                <td class="code">{{ $detalle->producto->item_codigo }}</td>
                <td>{{ $detalle->producto->descripcion }}</td>
                <td class="qty"><strong>{{ $detalle->cantidad }}</strong></td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="totals">
        <strong>Ítems:</strong> {{ $traspaso->detalles->count() }} &nbsp;|&nbsp;
        <strong>Unidades:</strong> {{ $totalItems }}
    </div>

    <div class="note">
        Movimiento interno entre sucursales. El stock se afecta según el estado del traspaso.
    </div>

    {{-- Firmas (compactas) --}}
    <div class="sign-row">
        <div class="sign">
            <hr class="line">
            <div class="who">Entregado por</div>
            <div class="hint">Nombre y firma</div>
        </div>
        <div class="sign">
            <hr class="line">
            <div class="who">Recibido por</div>
            <div class="hint">Nombre y firma</div>
        </div>
    </div>

    {{-- Footer pequeño (opcional, quítalo si no quieres ocupar espacio) --}}
    <div class="footer">
        <div>Emitido: {{ now()->format('d/m/Y H:i') }}</div>
        <div>YatiñaSoft</div>
        <div>Página 1 de 1</div>
    </div>

</body>
</html>
