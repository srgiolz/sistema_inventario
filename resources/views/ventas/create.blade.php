@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Registrar Venta</h2>
    <form method="POST" action="{{ route('ventas.store') }}">
        @csrf

        <!-- CLIENTE -->
        <div class="mb-3">
            <label>Cliente:</label>
            <select name="cliente_id" id="id_cliente" class="form-select compacto-input" required>
                <option value="">-- Selecciona un cliente --</option>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}">
                        {{ $cliente->nombre }} {{ $cliente->apellido }} - {{ $cliente->ci_nit ?? 'Sin CI' }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- SUCURSAL -->
        <div class="mb-3">
            <label>Sucursal:</label>
            <select name="sucursal_id" id="sucursal_id" class="form-select compacto-input" required>
                @foreach($sucursales as $sucursal)
                    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                @endforeach
            </select>
        </div>

        <!-- PRODUCTOS -->
        <h4>Productos</h4>
        <div id="productos-container">
            <div class="producto-item row gx-2 align-items-end mb-2 border-bottom pb-2">
                <div class="col-md-4">
                    <select name="productos[0][producto_id]" class="form-select producto-select" required>
                        <option value="">-- Producto --</option>
                        @foreach($productos as $producto)
                            <option value="{{ $producto->id }}"
                                data-precio="{{ $producto->precio_venta }}"
                                data-inventarios='@json($producto->inventarios->mapWithKeys(fn($inv) => [$inv->sucursal_id => $inv->cantidad]))'>
                                {{ $producto->descripcion }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <input type="number" name="productos[0][cantidad]" class="form-control cantidad-input" placeholder="Cant." min="1" required autocomplete="off">
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control precio-input" placeholder="Precio" readonly>
                    <input type="hidden" name="productos[0][precio]" class="precio-hidden">
                </div>
                <div class="col-md-2">
                    <input type="number" name="productos[0][descuento]" class="form-control descuento-input" placeholder="Desc. Bs" min="0" step="0.01" autocomplete="off">
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control stock-input" placeholder="Stock" readonly>
                </div>
                <div class="col-md-1 text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-circle shadow-sm eliminar-producto" title="Eliminar producto">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        </div>

        <button type="button" id="agregar-producto" class="btn btn-secondary mt-2 mb-3">+ Agregar producto</button>

        <!-- DESCUENTO GENERAL -->
        <div class="mb-3">
            <label>Descuento General Bs.:</label>
            <input type="number" id="descuento_total" name="descuento_total" class="form-control" value="0" min="0" step="0.01" autocomplete="off">
        </div>

        <!-- TOTAL -->
        <div class="mb-3">
            <label>Total Final:</label>
            <input type="text" id="total" name="total" class="form-control" readonly>
        </div>

       <!-- TIPO DE PAGO -->
<div class="mb-3">
    <label>Tipo de pago:</label>
    <select name="tipo_pago_id" class="form-select" required>
        <option value="">-- Selecciona --</option>
        @foreach($tiposPago as $tipo)
            <option value="{{ $tipo->id }}">{{ ucfirst($tipo->nombre) }}</option>
        @endforeach
    </select>
</div>


        <!-- CON FACTURA -->
        <div class="mb-3">
            <label>¿Venta con factura?</label>
            <select name="con_factura" class="form-select" required>
                <option value="1">Sí</option>
                <option value="0" selected>No</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary" id="btn-guardar">Guardar Venta</button>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {
    $('#id_cliente').select2({ placeholder: "Busca por nombre o CI...", width: '100%' });
    $('.producto-select').select2({ placeholder: "Selecciona un producto", width: '100%' });

    let index = 1;

    function generarSelectProducto(index) {
        let select = `<select name="productos[${index}][producto_id]" class="form-select producto-select" required>
            <option value="">-- Producto --</option>`;

        @foreach($productos as $producto)
            select += `<option value="{{ $producto->id }}"
                        data-precio="{{ $producto->precio_venta }}"
                        data-inventarios='@json($producto->inventarios->mapWithKeys(fn($inv) => [$inv->sucursal_id => $inv->cantidad]))'>
                        {{ $producto->descripcion }}
                    </option>`;
        @endforeach

        select += `</select>`;
        return select;
    }

    $('#agregar-producto').on('click', function () {
        const container = $('#productos-container');
        const original = container.find('.producto-item').first();
        const nuevaFila = original.clone(false);

        nuevaFila.find('input').each(function () {
            let name = $(this).attr('name');
            if (name?.includes('[cantidad]')) {
                $(this).attr('name', `productos[${index}][cantidad]`);
            } else if (name?.includes('[precio]')) {
                $(this).attr('name', `productos[${index}][precio]`);
            } else if (name?.includes('[descuento]')) {
                $(this).attr('name', `productos[${index}][descuento]`);
            }
            $(this).val('');
        });

        nuevaFila.find('.precio-input, .stock-input').val('');
        const nuevoSelect = generarSelectProducto(index);
        nuevaFila.find('.producto-select').parent().html(nuevoSelect);

        container.append(nuevaFila);
        container.find('.producto-item').last().find('.producto-select').select2({ placeholder: "Selecciona un producto", width: '100%' });
        index++;
    });

    $(document).on('click', '.eliminar-producto', function () {
        if ($('.producto-item').length > 1) {
            $(this).closest('.producto-item').remove();
            recalcularTotal();
        } else {
            alert('Debe haber al menos un producto.');
        }
    });

    $(document).on('change', '.producto-select', function () {
        let selected = this.options[this.selectedIndex];
        let precio = selected.getAttribute('data-precio');
        let inventarios = JSON.parse(selected.getAttribute('data-inventarios') || '{}');
        let sucursalId = $('#sucursal_id').val();
        let stock = inventarios[sucursalId] || 0;

        let fila = $(this).closest('.producto-item');
        fila.find('.precio-input').val(precio);
        fila.find('.precio-hidden').val(precio);
        fila.find('.stock-input').val(stock);

        recalcularTotal();
    });

    $(document).on('input change', '.cantidad-input, .descuento-input, #descuento_total', function () {
        recalcularTotal();
    });

    function recalcularTotal() {
        let total = 0;
        $('.producto-item').each(function () {
            let cant = parseFloat($(this).find('.cantidad-input').val()) || 0;
            let precio = parseFloat($(this).find('.precio-hidden').val()) || 0;
            let desc = parseFloat($(this).find('.descuento-input').val()) || 0;
            total += (cant * precio) - desc;
        });

        let descGeneral = parseFloat($('#descuento_total').val()) || 0;
        total -= descGeneral;
        $('#total').val(Math.max(0, total).toFixed(2));
    }

    $('form').on('submit', function (e) {
        e.preventDefault();

        const clienteId = $('#id_cliente').val();
        const clienteTexto = $('#id_cliente option:selected').text().trim();
        const tipoPago = $('select[name="tipo_pago_id"]').val();
        const total = $('#total').val() || '0.00';

        if (!clienteId) {
            Swal.fire({ icon: 'warning', title: 'Cliente no seleccionado', text: 'Debes seleccionar un cliente para registrar la venta.' });
            return;
        }

        if (!tipoPago) {
            Swal.fire({ icon: 'warning', title: 'Tipo de pago no seleccionado', text: 'Debes seleccionar un método de pago.' });
            return;
        }

        let cantidadTotal = 0;
        $('.producto-item').each(function () {
            let cant = parseFloat($(this).find('.cantidad-input').val()) || 0;
            if (cant > 0) {
                cantidadTotal += cant;
            }
        });

        if (cantidadTotal === 0 || parseFloat(total) <= 0) {
            Swal.fire({ icon: 'warning', title: 'Venta incompleta', text: 'Debes agregar al menos un producto con cantidad mayor a 0.' });
            return;
        }

        Swal.fire({
            title: '¿Confirmar venta?',
            html: `<b>Cliente:</b> ${clienteTexto}<br><b>Total:</b> Bs ${parseFloat(total).toFixed(2)}<br><b>Pago:</b> ${tipoPago}<br><b>Productos:</b> ${cantidadTotal} unidad(es)`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            preConfirm: () => {
                $('#btn-guardar').attr('disabled', true);
            }
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            } else {
                $('#btn-guardar').attr('disabled', false);
            }
        });
    });
});
</script>
@endpush




