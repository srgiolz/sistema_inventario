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
                    <th>Producto</th>
                    <th>Stock disponible</th>
                    <th>Cantidad</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($traspaso->detalles as $detalle)
                    <tr>
                        <td>
                            <select name="productos[]" class="form-control select-producto">
                                <option value="{{ $detalle->producto->id }}">
                                    {{ $detalle->producto->item_codigo }} - {{ $detalle->producto->descripcion }}
                                </option>
                            </select>
                        </td>
                        <td class="stock-disponible">--</td>
                        <td><input type="number" name="cantidades[]" value="{{ $detalle->cantidad }}" class="form-control cantidad" min="1" required></td>
                        <td><button type="button" class="btn btn-danger btn-sm eliminar-fila">🗑️</button></td>
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
        const sucursalId = {{ $traspaso->de_sucursal }};

        // Inicializar los selects existentes y cargar su stock
        $('.select-producto').each(function () {
            const select = $(this);
            const fila = select.closest('tr');
            const productoId = select.val();
            const stockTd = fila.find('.stock-disponible');

            select.select2({
                placeholder: 'Buscar producto',
                ajax: {
                    url: `/api/productos-por-sucursal/${sucursalId}`,
                    dataType: 'json',
                    processResults: function (data) {
                        return { results: data };
                    }
                }
            });

            if (productoId) {
                $.get(`/stock/${productoId}/${sucursalId}`, function (data) {
                    stockTd.text(data.stock);
                });
            }
        });

        // Al cambiar producto manualmente
        $('#tablaProductos').on('change', '.select-producto', function () {
            const fila = $(this).closest('tr');
            const productoId = $(this).val();
            const stockTd = fila.find('.stock-disponible');

            if (productoId) {
                $.get(`/stock/${productoId}/${sucursalId}`, function (data) {
                    stockTd.text(data.stock);
                });
            }
        });

        // Agregar nueva fila
        $('#agregarProducto').click(function () {
            const nuevaFila = `
                <tr>
                    <td>
                        <select name="productos[]" class="form-control select-producto" required></select>
                    </td>
                    <td class="stock-disponible">--</td>
                    <td><input type="number" name="cantidades[]" class="form-control cantidad" min="1" required></td>
                    <td><button type="button" class="btn btn-danger btn-sm eliminar-fila">🗑️</button></td>
                </tr>
            `;
            $('#tablaProductos tbody').append(nuevaFila);

            $('.select-producto').last().select2({
                placeholder: 'Buscar producto',
                ajax: {
                    url: `/api/productos-por-sucursal/${sucursalId}`,
                    dataType: 'json',
                    processResults: function (data) {
                        return { results: data };
                    }
                }
            });
        });

        // Eliminar fila
        $('#tablaProductos').on('click', '.eliminar-fila', function () {
            $(this).closest('tr').remove();
        });

        // ✅ Validar stock antes de enviar el formulario
        $('#formEditarTraspaso').on('submit', function (e) {
            let esValido = true;

            $('#tablaProductos tbody tr').each(function () {
                const fila = $(this);
                const stockTexto = fila.find('.stock-disponible').text();
                const stock = parseInt(stockTexto) || 0;
                const cantidad = parseInt(fila.find('.cantidad').val()) || 0;

                if (cantidad > stock) {
                    alert('❌ No puedes ingresar más cantidad de la disponible en stock.');
                    esValido = false;
                    return false; // salir del each
                }
            });

            if (!esValido) {
                e.preventDefault(); // prevenir envío del formulario
            }
        });
    });
</script>
@endpush

