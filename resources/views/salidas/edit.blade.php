@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 1100px;">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="bi bi-pencil-square text-warning me-2"></i>
            <span class="fw-semibold">Editar salida #{{ $salida->id }}</span>
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

    {{-- 🔒 Bloqueo si no está pendiente --}}
    @if($salida->estado !== 'pendiente')
        <div class="alert alert-warning">
            Esta salida está en estado <b>{{ strtoupper($salida->estado) }}</b> y no puede ser modificada.
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">

            <form action="{{ route('salidas.update', $salida->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Fecha / Sucursal / Tipo / Motivo --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Fecha</label>
                        <input type="date" name="fecha" class="form-control"
                               value="{{ \Carbon\Carbon::parse($salida->fecha)->format('Y-m-d') }}"
                               {{ $salida->estado !== 'pendiente' ? 'disabled' : '' }} required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Sucursal</label>
                        <select name="sucursal_id" id="sucursal_id" class="form-select"
                                {{ $salida->estado !== 'pendiente' ? 'disabled' : '' }} required>
                            @foreach($sucursales as $s)
                                <option value="{{ $s->id }}" {{ $salida->sucursal_id == $s->id ? 'selected' : '' }}>
                                    {{ $s->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Tipo</label>
                        <select name="tipo" class="form-select" {{ $salida->estado !== 'pendiente' ? 'disabled' : '' }} required>
                            <option value="Consumo interno" {{ $salida->tipo == 'Consumo interno' ? 'selected' : '' }}>Consumo interno</option>
                            <option value="Producto vencido" {{ $salida->tipo == 'Producto vencido' ? 'selected' : '' }}>Producto vencido</option>
                            <option value="Producto dañado" {{ $salida->tipo == 'Producto dañado' ? 'selected' : '' }}>Producto dañado</option>
                            <option value="Muestra médica" {{ $salida->tipo == 'Muestra médica' ? 'selected' : '' }}>Muestra médica</option>
                            <option value="Ajuste de inventario" {{ $salida->tipo == 'Ajuste de inventario' ? 'selected' : '' }}>Ajuste de inventario</option>
                            <option value="Otro" {{ $salida->tipo == 'Otro' ? 'selected' : '' }}>Otro</option>
                        </select>
                    </div>
                </div>

                {{-- Motivo / Observación --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Motivo</label>
                        <input type="text" name="motivo" class="form-control"
                               value="{{ $salida->motivo }}"
                               {{ $salida->estado !== 'pendiente' ? 'disabled' : '' }} required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Observación</label>
                        <textarea name="observacion" class="form-control" rows="2"
                                  {{ $salida->estado !== 'pendiente' ? 'disabled' : '' }}>{{ $salida->observacion }}</textarea>
                    </div>
                </div>

                {{-- Productos --}}
                <h6 class="fw-semibold text-body mb-2">Productos en la salida</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped align-middle mb-2" id="tabla-productos">
                        <thead class="table-light text-center align-middle">
                            <tr>
                                <th style="width: 44%">Producto</th>
                                <th style="width: 14%">Stock disponible</th>
                                <th style="width: 14%">Cantidad</th>
                                <th style="width: 10%">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salida->detalles as $i => $detalle)
                                <tr>
                                    <td>
                                        <select name="productos[{{ $i }}][producto_id]" class="form-control select-producto" required
                                            data-selected="{{ $detalle->producto->id }}"
                                            {{ $salida->estado !== 'pendiente' ? 'disabled' : '' }}>
                                            <option value="{{ $detalle->producto->id }}" selected>
                                                {{ $detalle->producto->codigo_item }} - {{ $detalle->producto->descripcion }}
                                            </option>
                                        </select>
                                    </td>
                                    <td class="text-center">
                                        <input type="text" class="form-control stock text-center" readonly>
                                    </td>
                                    <td class="text-center">
                                        <input type="number" name="productos[{{ $i }}][cantidad]"
                                               class="form-control cantidad text-center"
                                               value="{{ $detalle->cantidad }}" min="1"
                                               {{ $salida->estado !== 'pendiente' ? 'disabled' : '' }} required>
                                    </td>
                                    <td class="text-center">
                                        @if($salida->estado === 'pendiente')
                                            <button type="button" class="btn btn-outline-danger btn-sm eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($salida->estado === 'pendiente')
                    <button type="button" id="agregar-producto" class="btn btn-outline-secondary btn-sm mb-3">
                        <i class="bi bi-plus-circle me-1"></i> Agregar producto
                    </button>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="{{ route('salidas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="button" id="btnGuardar" class="btn btn-warning">
                            <i class="bi bi-check2-circle me-1"></i> Guardar cambios
                        </button>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>

{{-- ===== Estilos ===== --}}
@push('styles')
<style>
    .table-danger { background-color: #ffeaea !important; transition: background-color 0.3s ease; }
</style>
@endpush

{{-- ===== Scripts ===== --}}
@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let index = {{ $salida->detalles->count() }};
const sucursalId = $('#sucursal_id').val();

function inicializarSelect2(element) {
    $(element).select2({
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

function actualizarStock(selectElement) {
    const idProducto = selectElement.val();
    const fila = selectElement.closest('tr');
    if (idProducto && sucursalId) {
        fetch(`/stock/${idProducto}/${sucursalId}`)
            .then(res => res.json())
            .then(data => { fila.find('.stock').val(data.stock); });
    }
}

$(function () {
    // Cargar stock actual
    $('.select-producto').each(function () {
        inicializarSelect2(this);
        actualizarStock($(this));
    });

    // Agregar producto
    $('#agregar-producto').on('click', function () {
        const fila = `
            <tr>
                <td><select name="productos[${index}][producto_id]" class="form-control select-producto" required></select></td>
                <td class="text-center"><input type="text" class="form-control stock text-center" readonly></td>
                <td class="text-center"><input type="number" name="productos[${index}][cantidad]" class="form-control cantidad text-center" min="1" required></td>
                <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm eliminar"><i class="bi bi-trash"></i></button></td>
            </tr>`;
        $('#tabla-productos tbody').append(fila);
        inicializarSelect2($('.select-producto').last());
        index++;
    });

    // Eliminar fila
    $('#tabla-productos').on('click', '.eliminar', function () {
        $(this).closest('tr').remove();
    });

    // Evitar duplicados
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

// ✅ Validación antes de guardar (versión mejorada)
$('#btnGuardar').on('click', function (e) {
    e.preventDefault();

    const filas = $('#tabla-productos tbody tr').length;

    // 🚨 Sin filas
    if (filas === 0) {
        Swal.fire({
            icon: 'info',
            title: 'Sin productos',
            text: 'Debes agregar al menos un producto para continuar.',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    let errorStock = false;
    let camposVacios = false;
    let duplicados = false;
    const productosSeleccionados = [];

    // Recorremos cada fila
    $('#tabla-productos tbody tr').each(function () {
        const producto = $(this).find('.select-producto').val();
        const stock = parseInt($(this).find('.stock').val()) || 0;
        const cantidad = parseInt($(this).find('.cantidad').val()) || 0;

        // 🚨 Validar vacíos
        if (!producto || !cantidad || cantidad <= 0) {
            camposVacios = true;
            $(this).addClass('table-danger');
            return; // pasa a la siguiente fila
        } else {
            $(this).removeClass('table-danger');
        }

        // 🚨 Validar duplicados
        if (productosSeleccionados.includes(producto)) {
            duplicados = true;
        } else {
            productosSeleccionados.push(producto);
        }

        // 🚨 Validar stock
        if (cantidad > stock) {
            errorStock = true;
            $(this).addClass('table-danger');
        } else {
            $(this).removeClass('table-danger');
        }
    });

    if (camposVacios) {
        Swal.fire({
            icon: 'error',
            title: 'Campos incompletos',
            text: 'Debes seleccionar un producto y una cantidad válida en todas las filas.',
            confirmButtonText: 'Corregir'
        });
        return;
    }

    if (duplicados) {
        Swal.fire({
            icon: 'warning',
            title: 'Productos duplicados',
            text: 'Existen productos repetidos. Elimina los duplicados antes de continuar.',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    if (errorStock) {
        Swal.fire({
            icon: 'error',
            title: 'Cantidad excedida',
            text: 'Uno o más productos superan el stock disponible. Corrige las cantidades antes de continuar.',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    // ✅ Si todo está correcto → confirmar guardado
    Swal.fire({
        title: '¿Guardar cambios?',
        text: 'Se actualizará la información de la salida.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then(result => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Salida actualizada',
                text: 'Los cambios fueron guardados correctamente.',
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
                willClose: () => {
                    $('form').submit();
                    setTimeout(() => {
                        window.location.href = "{{ route('salidas.index') }}";
                    }, 1800);
                }
            });
        }
    });
});
});
</script>
@endpush
@endsection
