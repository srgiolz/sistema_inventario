@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">
        <i class="bi bi-pencil-square text-warning"></i> 
        Editar <span class="fw-bold">Traspaso #{{ $traspaso->id }}</span>
    </h4>

    <form action="{{ route('traspasos.update', $traspaso->id) }}" method="POST" id="formEditarTraspaso">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            {{-- Origen (bloqueado) --}}
            <div class="col-md-6">
                <label class="form-label fw-bold">Sucursal Origen</label>
                <input type="text" class="form-control" value="{{ $traspaso->sucursalOrigen->nombre }}" disabled>
                <input type="hidden" name="de_sucursal" value="{{ $traspaso->sucursal_origen_id }}">
            </div>

            {{-- Destino (editable) --}}
            <div class="col-md-6">
                <label for="a_sucursal" class="form-label fw-bold">Sucursal Destino</label>
                <select name="a_sucursal" id="a_sucursal" class="form-control" required>
                    @foreach($sucursales as $sucursal)
                        @if($sucursal->id != $traspaso->sucursal_origen_id)
                            <option value="{{ $sucursal->id }}"
                                {{ $sucursal->id == $traspaso->sucursal_destino_id ? 'selected' : '' }}>
                                {{ $sucursal->nombre }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Fecha (solo lectura) --}}
        <div class="mb-3">
            <label class="form-label fw-bold">Fecha</label>
            <input type="text" class="form-control"
                   value="{{ \Carbon\Carbon::parse($traspaso->fecha)->format('d/m/Y') }}" disabled>
        </div>

        {{-- Observación --}}
        <div class="mb-3">
            <label for="observacion" class="form-label fw-bold">Observación</label>
            <textarea name="observacion" id="observacion" rows="2"
                      class="form-control">{{ old('observacion', $traspaso->observacion) }}</textarea>
        </div>

        {{-- Productos --}}
        <h5 class="mt-4">📦 Productos del Traspaso</h5>
        <table class="table table-sm table-bordered align-middle" id="tablaProductos">
            <thead class="table-light">
                <tr>
                    <th style="width:55%">Producto</th>
                    <th style="width:15%" class="text-center">Stock actual</th>
                    <th style="width:20%" class="text-center">Cantidad</th>
                    <th style="width:10%" class="text-center">Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($traspaso->detalles as $detalle)
                    <tr>
                        <td>
                            <select name="productos[{{ $loop->index }}][producto_id]"
                                    class="form-control select-producto" required>
                                <option value="{{ $detalle->producto->id }}" selected>
                                    {{ $detalle->producto->codigo_item }} - {{ $detalle->producto->descripcion }}
                                </option>
                            </select>
                        </td>
                        <td class="stock-disponible text-center">0</td>
                        <td>
                            <input type="number"
                                   name="productos[{{ $loop->index }}][cantidad]"
                                   value="{{ $detalle->cantidad }}"
                                   class="form-control text-center cantidad"
                                   min="1"
                                   required>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm eliminar-fila">🗑️</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button type="button" id="agregarProducto" class="btn btn-outline-primary mb-3">
            <i class="bi bi-plus-circle"></i> Agregar Producto
        </button>

        <div class="mt-4">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-save"></i> Guardar Cambios
            </button>
            <a href="{{ route('traspasos.show', $traspaso->id) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Cancelar
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    const sucursalId = {{ $traspaso->sucursal_origen_id }};

    // Inicializar Select2 para productos
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

    // Cargar stock real desde inventario
    function cargarStock($row) {
        const $stockTd = $row.find('.stock-disponible');
        const productoId = $row.find('.select-producto').val();

        if (!productoId) { $stockTd.text("0"); return; }

        $.get(`/stock/${productoId}/${sucursalId}`, function (data) {
            const base = (data && data.stock !== undefined) ? parseInt(data.stock, 10) : 0;
            $stockTd.text(base);
        }).fail(function () {
            $stockTd.text("0");
        });
    }

    // Inicializar productos existentes
    $('.select-producto').each(function () {
        const $select = $(this);
        initSelect2($select);
        cargarStock($select.closest('tr'));
    });

    // Al cambiar un producto, refrescar stock
    $('#tablaProductos').on('change', '.select-producto', function () {
        cargarStock($(this).closest('tr'));
    });

    // Agregar nueva fila
    let idx = $('#tablaProductos tbody tr').length;
    $('#agregarProducto').on('click', function () {
        const nuevaFila = `
            <tr>
                <td>
                    <select name="productos[${idx}][producto_id]" class="form-control select-producto" required></select>
                </td>
                <td class="stock-disponible text-center">0</td>
                <td>
                    <input type="number" name="productos[${idx}][cantidad]" class="form-control text-center cantidad" min="1" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm eliminar-fila">🗑️</button>
                </td>
            </tr>
        `;
        $('#tablaProductos tbody').append(nuevaFila);

        const $nuevoSelect = $('#tablaProductos tbody tr:last .select-producto');
        initSelect2($nuevoSelect);
        cargarStock($nuevoSelect.closest('tr'));
        idx++;
    });

    // Eliminar fila
    $('#tablaProductos').on('click', '.eliminar-fila', function () {
        $(this).closest('tr').remove();
    });

    // Validaciones antes de enviar
    $('#formEditarTraspaso').on('submit', function (e) {
        let ok = true;

        $('#tablaProductos tbody tr').each(function () {
            const $row = $(this);
            const stockReal = parseInt($row.find('.stock-disponible').text(), 10) || 0;
            const cantidad = parseInt($row.find('.cantidad').val(), 10) || 0;

            if (cantidad <= 0) {
                alert('❌ La cantidad debe ser mayor a 0.');
                ok = false; return false;
            }
            if (cantidad > stockReal) {
                alert('❌ No puedes traspasar más de lo disponible en inventario.');
                ok = false; return false;
            }
        });

        if (!ok) e.preventDefault();
    });
});
</script>
@endpush
