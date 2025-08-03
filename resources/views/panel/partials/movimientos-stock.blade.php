<div class="table-responsive">
    <table class="table table-sm table-bordered text-center align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Sucursal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimientos as $mov)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y H:i') }}</td>
                    <td>
                        <span class="badge bg-{{ $mov->tipo == 'entrada' ? 'success' : ($mov->tipo == 'salida' ? 'danger' : 'info') }}">
                            {{ ucfirst($mov->tipo) }}
                        </span>
                    </td>
                    <td class="text-start">{{ $mov->producto }}</td>
                    <td><strong>{{ $mov->cantidad }}</strong></td>
                    <td>{{ $mov->sucursal }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-muted">No se encontraron movimientos para ese rango.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
