@extends('layouts.app')

@section('content')
<div class="container">
    <h4>✏️ Editar Traspaso #{{ $traspaso->id }}</h4>

    <form action="{{ route('traspasos.update', $traspaso->id) }}" method="POST" id="formEditarTraspaso">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <strong>Origen:</strong> {{ $traspaso->sucursalOrigen->nombre }}<br>
            <strong>Destino:</strong> {{ $traspaso->sucursalDestino->nombre }}<br>
            <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($traspaso->fecha)->format('d/m/Y') }}<br>
        </div>

        <div class="mb-3">
            <label for="observacion" class="form-label">Observación</label>
            <textarea name="observacion" id="observacion" class="form-control">{{ old('observacion', $traspaso->observacion) }}</textarea>
        </div>

        <h5>📦 Productos</h5>
        <table class="table table-bordered" id="tablaProductos">
            <thead>
                <tr>
                    <th style="width:55%">Producto</th>
                    <th style="width:15%">Stock disponible</th>
                    <th style="width:20%">Cantidad</th>
                    <th style="width:10%">Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($traspaso->detalles as $detalle)
                    <tr>
                        <td>
                            <select name="productos[{{ $loop->index }}][producto_id]" class="form-control select-producto" required>
                                <option value="{{ $detalle->producto->id }}" selected>
                                    {{ $detalle->producto->codigo_item }} - {{ $detalle->producto->descripcion }}
                                </option>
                            </select>
                        </td>
                        <td class="stock-disponible">0</td>
                        <td>
                            <input type="number"
                                   name="productos[{{ $loop->index }}][cantidad]"
                                   value="{{ $detalle->cantidad }}"
                                   class="form-control cantidad"
                                   min="1"
                                   required>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm eliminar-fila">🗑️</button>
                        </td>

                        {{-- Cantidad original de ESTE traspaso para este producto (stock editable) --}}
                        <input type="hidden" class="cantidad-original" value="{{ $detalle->cantidad }}">
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button type="button" id="agregarProducto" class="btn btn-outline-primary mb-3">➕ Agregar Producto</button>
        <br>

        <button type="submit" class="btn btn-success">💾 Guardar Cambios</button>
        <a href="{{ route('traspasos.show', $traspaso->id) }}" class="btn btn-secondary">🔙 Cancelar</a>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    // Campo correcto: sucursal ORIGEN
    const sucursalId = {{ $traspaso->sucursal_origen_id }};

    function initSelect2($select) {
        $select.select2({
            placeholder: 'Buscar producto',
            width: '100%',
            ajax: {
                url: `/api/productos-por-sucursal/${sucursalId}`,
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return { results: data };
                }
            }
        });
    }

    // Cargar stock "editable" = stock real + cantidad original del detalle
    function cargarStockEditable($row) {
        const $stockTd = $row.find('.stock-disponible');
        const productoId = $row.find('.select-producto').val();
        const original = parseInt($row.find('.cantidad-original').val(), 10) || 0;

        if (!productoId) { $stockTd.text(original); return; }

        $.get(`/stock/${productoId}/${sucursalId}`, function (data) {
            const base = (data && data.stock !== undefined) ? parseInt(data.stock, 10) : 0;
            $stockTd.text(base + original); // stock editable
        }).fail(function () {
            $stockTd.text(original);        // al menos refleja lo original
        });
    }

    // Inicializar filas existentes
    $('.select-producto').each(function () {
        const $select = $(this);
        initSelect2($select);
        cargarStockEditable($select.closest('tr'));
    });

    // Si cambian el producto en una fila ya existente:
    // - el "original" ya no aplica → set 0
    // - recargar stock para ese nuevo producto
    $('#tablaProductos').on('change', '.select-producto', function () {
        const $row = $(this).closest('tr');
        $row.find('.cantidad-original').val(0);
        cargarStockEditable($row);
    });

    // Índice para nuevas filas
    let idx = $('#tablaProductos tbody tr').length;

    // Agregar nueva fila
    $('#agregarProducto').on('click', function () {
        const nuevaFila = `
            <tr>
                <td>
                    <select name="productos[${idx}][producto_id]" class="form-control select-producto" required></select>
                </td>
                <td class="stock-disponible">0</td>
                <td>
                    <input type="number" name="productos[${idx}][cantidad]" class="form-control cantidad" min="1" required>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm eliminar-fila">🗑️</button>
                </td>
                <input type="hidden" class="cantidad-original" value="0">
            </tr>
        `;
        $('#tablaProductos tbody').append(nuevaFila);

        const $nuevoSelect = $('#tablaProductos tbody tr:last .select-producto');
        initSelect2($nuevoSelect);
        cargarStockEditable($nuevoSelect.closest('tr'));
        idx++;
    });

    // Eliminar fila
    $('#tablaProductos').on('click', '.eliminar-fila', function () {
        $(this).closest('tr').remove();
    });

    // Validación antes de enviar
    $('#formEditarTraspaso').on('submit', function (e) {
        let ok = true;

        $('#tablaProductos tbody tr').each(function () {
            const $row = $(this);
            const stockEditable = parseInt($row.find('.stock-disponible').text(), 10) || 0;
            const cantidad = parseInt($row.find('.cantidad').val(), 10) || 0;

            if (cantidad <= 0) {
                alert('La cantidad debe ser mayor a 0.');
                ok = false; return false;
            }
            if (cantidad > stockEditable) {
                alert('❌ No puedes traspasar más de lo disponible (considerando lo ya asignado en este traspaso).');
                ok = false; return false;
            }
        });

        if (!ok) e.preventDefault();
    });
});
</script>
@endpush
