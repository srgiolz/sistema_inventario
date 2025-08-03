<div class="table-responsive">
    <table class="table table-sm align-middle text-center table-bordered mb-0">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th class="text-start">Producto</th>
                <th>Línea</th>
                <th>Stock actual</th>
            </tr>
        </thead>
        <tbody>
            @forelse($productosSinVentas30d as $index => $producto)
                <tr>
                    <td class="bg-light fw-bold">{{ $index + 1 }}</td>
                    <td class="text-start" title="{{ $producto->descripcion }}">
                        {{ Str::limit($producto->descripcion, 50) }}
                    </td>
                    <td>{{ $producto->linea->nombre ?? '-' }}</td>
                    <td>
                        <span class="badge bg-danger-subtle text-danger fw-semibold">
                            {{ $producto->inventario->cantidad ?? 0 }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-muted">No se encontraron productos sin ventas en esta línea.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
