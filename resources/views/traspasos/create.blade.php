@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 1100px;">

    {{-- Encabezado + botón ghost al historial --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="bi bi-arrow-left-right text-success me-2"></i>
            <span class="fw-semibold">Registrar traspaso entre sucursales</span>
        </h4>
        <a href="{{ route('traspasos.index') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-clock-history me-1"></i> Historial de Traspasos
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

            {{-- ===== Datos del traspaso ===== --}}
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-2 fw-semibold text-body">Datos del traspaso</h6>
            </div>
            <hr class="mt-0 mb-3">

            <form action="{{ route('traspasos.store') }}" method="POST">
                @csrf

                {{-- Origen / Destino --}}
                <div class="row g-3 mb-2">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">De sucursal (origen)</label>
                        <select name="de_sucursal" id="de_sucursal" class="form-select" required>
                            <option value="">— Seleccionar —</option>
                            @foreach($sucursales as $s)
                                <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">A sucursal (destino)</label>
                        <select name="a_sucursal" id="a_sucursal" class="form-select" required>
                            <option value="">— Seleccionar —</option>
                            @foreach($sucursales as $s)
                                <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Fecha / Observación --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Fecha</label>
                        <input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Observación (opcional)</label>
                        <textarea name="observacion" class="form-control" rows="2" placeholder="Motivo o notas del traspaso"></textarea>
                    </div>
                </div>

                {{-- ===== Productos a traspasar ===== --}}
                <h6 class="fw-semibold text-body mb-2">Productos a traspasar</h6>
                <div class="small text-muted mb-2">Solo aparecerán productos con stock en la sucursal de <b>origen</b>.</div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped align-middle mb-2" id="tabla-productos">
                        <thead class="table-light text-center align-middle">
                            <tr>
                                <th style="width: 44%">Producto</th>
                                <th style="width: 14%">Stock disponible</th>
                                <th style="width: 14%">Cantidad a enviar</th>
                                <th style="width: 10%">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- filas dinámicas via JS --}}
                        </tbody>
                    </table>
                </div>

                <button type="button" id="agregar-producto" class="btn btn-outline-secondary btn-sm mb-3">
                    <i class="bi bi-plus-circle me-1"></i> Agregar producto
                </button>

                {{-- Barra de acciones --}}
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('traspasos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="button" id="btnRegistrar" class="btn btn-primary">
                        <i class="bi bi-check2-circle me-1"></i> Registrar traspaso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== Estilos puntuales (ligeros) ===== --}}
@push('styles')
<style>
    .select2-container--default .select2-selection--single {
        height: 38px; padding: 6px 10px; border: 1px solid #ced4da; border-radius: .375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 25px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
    #tabla-productos td .form-control.text-center { text-align: center; }
</style>
@endpush

{{-- ===== Dependencias y JS (tu misma lógica) ===== --}}
@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let index = 0;

function crearFilaProducto() {
    const sucursalId = $('#de_sucursal').val();
    if (!sucursalId) { alert('Selecciona primero la sucursal de origen.'); return; }

    const fila = `
        <tr>
            <td>
                <select name="productos[${index}][producto_id]" class="form-control select-producto" required></select>
            </td>
            <td class="text-center">
                <input type="text" class="form-control stock text-center" readonly>
            </td>
            <td class="text-center">
                <input type="number" name="productos[${index}][cantidad]" class="form-control cantidad text-center" min="1" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm eliminar" title="Eliminar">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>`;
    $('#tabla-productos tbody').append(fila);
    inicializarSelect2(sucursalId);
    index++;
}

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

function actualizarStock(selectElement) {
    const idProducto = selectElement.val();
    const idSucursal = $('#de_sucursal').val();
    const fila = selectElement.closest('tr');
    if (idProducto && idSucursal) {
        fetch(`/stock/${idProducto}/${idSucursal}`)
            .then(res => res.json())
            .then(data => { fila.find('.stock').val(data.stock); });
    } else {
        fila.find('.stock').val('');
    }
}

$(function () {
    $('#agregar-producto').on('click', crearFilaProducto);
    $('#tabla-productos').on('click', '.eliminar', function () { $(this).closest('tr').remove(); });

    // ✅ Limpiar tabla si cambia sucursal de origen
    $('#de_sucursal').on('change', function () {
        $('#tabla-productos tbody').empty();
        index = 0;
    });

    // ✅ Evitar productos duplicados en la tabla
    $('#tabla-productos').on('change', '.select-producto', function () {
        const seleccionado = $(this).val();
        let repetido = false;

        $('.select-producto').not(this).each(function () {
            if ($(this).val() === seleccionado) {
                repetido = true;
            }
        });

        if (repetido) {
            alert('⚠️ Ese producto ya fue agregado. Elige otro.');
            $(this).val(null).trigger('change'); // limpia el select
        } else {
            actualizarStock($(this)); // si no es repetido, actualiza stock
        }
    });

    // Validación antes de enviar
    $('form').on('submit', function (e) {
        let ok = true;
        $('#tabla-productos tbody tr').each(function () {
            const stock = parseInt($(this).find('.stock').val()) || 0;
            const cantidad = parseInt($(this).find('.cantidad').val()) || 0;
            if (cantidad > stock) {
                alert('No puedes traspasar más de lo disponible.');
                ok = false;
                return false;
            }
        });
        if (!ok) e.preventDefault();
    });
});

// Confirmación con SweetAlert
$('#btnRegistrar').on('click', function (e) {
    e.preventDefault();
    Swal.fire({
        title: '¿Registrar traspaso?',
        text: 'Verifica que los productos y cantidades sean correctos.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, registrar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $('form').submit(); // ✅ envía el formulario solo si confirma
        }
    });
});
</script>
@endpush
@endsection

