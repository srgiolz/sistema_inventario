<div class="mt-2 small">
    @foreach ($ventasPorTipoPago as $tipo => $total)
        <div>
            <i class="bi bi-currency-dollar"></i>
            <strong>{{ ucfirst($tipo) }}:</strong> Bs {{ number_format($total, 2) }}
        </div>
    @endforeach
</div>
