@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 1100px;">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="bi bi-box-arrow-up text-primary me-2"></i>
            <span class="fw-semibold">Registrar salida de productos</span>
        </h4>
        <a href="{{ route('salidas.index') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-clock-history me-1"></i> Historial de Salidas
        </a>
    </div>

    {{-- Mensajes de error --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">

            <form id="form-salida" action="{{ route('salidas.store') }}" method="POST">
                @csrf

                {{-- Datos generales --}}
                <h6 class="fw-semibold text-body mb-3">Datos de la salida</h6>
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Fecha</label>
                        <input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Sucursal origen</label>
                        <select name="sucursal_id" id="sucursal_id" class="form-select" required>
                            <option value="">— Seleccionar —</option>
                            @foreach($sucursales as $s)
                                <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Tipo de salida</label>
                        <select name="tipo" class="form-select" required>
                            <option value="">— Seleccionar —</option>
                            <option value="Consumo interno">Consumo interno</option>
                            <option value="Producto vencido">Producto vencido</option>
                            <option value="Producto dañado">Producto dañado</option>
                            <option value="Muestra médica">Muestra médica</option>
                            <option value="Ajuste de inventario">Ajuste de inventario</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Motivo</label>
                        <input type="text" name="motivo" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Observación (opcional)</label>
                        <textarea name="observacion" class="form-control" rows="1"></textarea>
                    </div>
                </div>

                {{-- Productos --}}
                <h6 class="fw-semibold text-body mb-2">Productos a retirar</h6>
                <div class="small text-muted mb-2">Solo aparecerán productos con stock disponible en la sucursal seleccionada.</div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped align-middle mb-2" id="tabla-productos">
                        <thead class="table-light text-center align-middle">
                            <tr>
                                <th style="width: 44%">Producto</th>
                                <th style="width: 14%">Stock actual</th>
                                <th style="width: 14%">Cantidad</th>
                                <th style="width: 10%">Acción</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <button type="button" id="agregar-producto" class="btn btn-outline-secondary btn-sm mb-3">
                    <i class="bi bi-plus-circle me-1"></i> Agregar producto
                </button>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('salidas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="button" id="btnRegistrar" class="btn btn-primary">
                        <i class="bi bi-check2-circle me-1"></i> Registrar salida
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Estilos --}}
@push('styles')
<style>
.select2-container--default .select2-selection--single {
    height: 38px; padding: 6px 10px; border: 1px solid #ced4da; border-radius: .375rem;
}
.select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 25px; }
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
#tabla-productos td .form-control.text-center { text-align: center; }
.table-danger { background-color: #ffeaea !important; transition: background-color 0.3s ease; }
</style>
@endpush

{{-- Scripts --}}
@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let index = 0;

// ✅ Crear fila
function crearFilaProducto() {
    const sucursalId = $('#sucursal_id').val();
    if (!sucursalId) {
        Swal.fire({ icon: 'info', title: 'Selecciona una sucursal primero', confirmButtonText: 'Entendido' });
        return;
    }

    const fila = `
        <tr>
            <td><select name="productos[${index}][producto_id]" class="form-control select-producto" required></select></td>
            <td class="text-center"><input type="text" class="form-control stock text-center" readonly></td>
            <td class="text-center"><input type="number" name="productos[${index}][cantidad]" class="form-control cantidad text-center" min="1" required></td>
            <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm eliminar"><i class="bi bi-trash"></i></button></td>
        </tr>`;
    $('#tabla-productos tbody').append(fila);
    inicializarSelect2(sucursalId);
    index++;
}

// ✅ Inicializa Select2 y carga stock
function inicializarSelect2(sucursalId) {
    $('.select-producto').last().select2({
        placeholder: 'Buscar producto…',
        width: '100%',
        ajax: {
            url: "{{ url('/api/productos-por-sucursal') }}/" + sucursalId,
            dataType: 'json',
            delay: 250,
            data: params => ({ term: params.term || '' }),
            processResults: data => ({ results: data })
        }
    });
}

// ✅ Actualiza stock del producto seleccionado
function actualizarStock(selectElement) {
    const idProducto = selectElement.val();
    const idSucursal = $('#sucursal_id').val();
    const fila = selectElement.closest('tr');
    if (idProducto && idSucursal) {
        fetch(`/stock/${idProducto}/${idSucursal}`)
            .then(res => res.json())
            .then(data => { fila.find('.stock').val(data.stock); });
    } else {
        fila.find('.stock').val('');
    }
}

// ✅ Eventos principales
$(function () {
    $('#agregar-producto').on('click', crearFilaProducto);
    $('#tabla-productos').on('click', '.eliminar', function () { $(this).closest('tr').remove(); });

    // Limpiar tabla si cambia la sucursal
    $('#sucursal_id').on('change', function () {
        $('#tabla-productos tbody').empty();
        index = 0;
    });

    // Evitar productos duplicados
    $('#tabla-productos').on('change', '.select-producto', function () {
        const seleccionado = $(this).val();
        let repetido = false;

        $('.select-producto').not(this).each(function () {
            if ($(this).val() === seleccionado) repetido = true;
        });

        if (repetido) {
            Swal.fire({ icon: 'warning', title: 'Producto duplicado', text: 'Ese producto ya fue agregado.' });
            $(this).val(null).trigger('change');
        } else {
            actualizarStock($(this));
        }
    });

// ✅ Validación antes de registrar
$('#btnRegistrar').on('click', function (e) {
    e.preventDefault();

    const filas = $('#tabla-productos tbody tr');
    if (filas.length === 0) {
        Swal.fire({
            icon: 'info',
            title: 'Sin productos',
            text: 'Debes agregar al menos un producto para registrar la salida.'
        });
        return;
    }

    let errorStock = false;
    let errorProducto = false;
    let errorCantidad = false;

    filas.each(function () {
        const producto = $(this).find('.select-producto').val();
        const stock = parseInt($(this).find('.stock').val()) || 0;
        const cantidad = parseInt($(this).find('.cantidad').val()) || 0;

        // ❌ Producto no seleccionado
        if (!producto) {
            $(this).addClass('table-danger');
            errorProducto = true;
        }

        // ❌ Cantidad vacía o inválida
        if (!cantidad || cantidad <= 0) {
            $(this).addClass('table-danger');
            errorCantidad = true;
        }

        // ❌ Cantidad mayor al stock
        if (cantidad > stock) {
            $(this).addClass('table-danger');
            errorStock = true;
        }

        if (producto && cantidad > 0 && cantidad <= stock) {
            $(this).removeClass('table-danger');
        }
    });

    if (errorProducto) {
        Swal.fire({
            icon: 'warning',
            title: 'Producto sin seleccionar',
            text: 'Verifica que todas las filas tengan un producto seleccionado.'
        });
        return;
    }

    if (errorCantidad) {
        Swal.fire({
            icon: 'warning',
            title: 'Cantidad inválida',
            text: 'Hay filas con cantidad vacía o menor a 1. Corrige antes de continuar.'
        });
        return;
    }

    if (errorStock) {
        Swal.fire({
            icon: 'error',
            title: 'Cantidad excedida',
            text: 'Uno o más productos superan el stock disponible. Corrige las cantidades antes de continuar.'
        });
        return;
    }

    // ✅ Si todo está correcto
    Swal.fire({
        title: '¿Registrar salida?',
        text: 'Verifica que los productos y cantidades sean correctos.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, registrar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Salida registrada con éxito',
                text: 'Redirigiendo al historial...',
                showConfirmButton: false,
                timer: 1800,
                timerProgressBar: true,
                willClose: () => {
                    $('#form-salida').submit();
                }
            });
        }
    });
});
});
</script>
@endpush
@endsection

