@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 1100px;">

    <h4 class="mb-3">
        <i class="bi bi-pencil-square text-warning me-2"></i>
        Editar traspaso #{{ $traspaso->id }}
    </h4>

    {{-- Errores --}}
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
            <form action="{{ route('traspasos.update', $traspaso->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Origen / Destino --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">De sucursal (origen)</label>
                        <select name="de_sucursal" id="de_sucursal" class="form-select" 
                            {{ $traspaso->detalles->count() > 0 ? 'disabled' : '' }} required>
                            @foreach($sucursales as $s)
                                <option value="{{ $s->id }}" 
                                    {{ $traspaso->sucursal_origen_id == $s->id ? 'selected' : '' }}>
                                    {{ $s->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @if($traspaso->detalles->count() > 0)
                            <input type="hidden" name="de_sucursal" value="{{ $traspaso->sucursal_origen_id }}">
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">A sucursal (destino)</label>
                        <select name="a_sucursal" class="form-select" required>
                            @foreach($sucursales as $s)
                                <option value="{{ $s->id }}" 
                                    {{ $traspaso->sucursal_destino_id == $s->id ? 'selected' : '' }}>
                                    {{ $s->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Observación --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Observación</label>
                    <textarea name="observacion" class="form-control" rows="2">{{ $traspaso->observacion }}</textarea>
                </div>

                {{-- Productos --}}
                <h6 class="fw-semibold mb-2">Productos del traspaso</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle" id="tabla-productos">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="width: 44%">Producto</th>
                                <th style="width: 14%">Stock disponible</th>
                                <th style="width: 14%">Cantidad</th>
                                <th style="width: 10%">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($traspaso->detalles as $i => $detalle)
                                <tr>
                                    <td>
                                        <select name="productos[{{ $i }}][producto_id]" 
                                                class="form-control select-producto" required>
                                            <option value="{{ $detalle->producto->id }}" selected>
                                                {{ $detalle->producto->codigo_item }} - {{ $detalle->producto->descripcion }}
                                            </option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control stock text-center" readonly value="--">
                                    </td>
                                    <td>
                                        <input type="number" name="productos[{{ $i }}][cantidad]" 
                                               class="form-control cantidad text-center" 
                                               value="{{ $detalle->cantidad }}" min="1" required>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <button type="button" id="agregar-producto" class="btn btn-outline-secondary btn-sm mt-2">
                    <i class="bi bi-plus-circle me-1"></i> Agregar producto
                </button>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('traspasos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" id="btnActualizar" class="btn btn-warning">
                        <i class="bi bi-check2-circle me-1"></i> Actualizar traspaso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Scripts --}}
@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let index = {{ count($traspaso->detalles) }};

function crearFilaProducto() {
    const sucursalId = $('#de_sucursal').val();
    if (!sucursalId) { alert('Selecciona primero la sucursal de origen.'); return; }

    const fila = `
        <tr>
            <td>
                <select name="productos[${index}][producto_id]" class="form-control select-producto" required></select>
            </td>
            <td><input type="text" class="form-control stock text-center" readonly></td>
            <td><input type="number" name="productos[${index}][cantidad]" class="form-control cantidad text-center" min="1" required></td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm eliminar"><i class="bi bi-trash"></i></button>
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
    const sucursalId = $('#de_sucursal').val();

    // Inicializar Select2 y cargar stock de productos existentes
    $('.select-producto').each(function () {
        inicializarSelect2(sucursalId);
        actualizarStock($(this));
    });

    // Agregar producto
    $('#agregar-producto').on('click', crearFilaProducto);

    // Eliminar producto
    $('#tabla-productos').on('click', '.eliminar', function () {
        $(this).closest('tr').remove();
    });

    // Validar duplicados y actualizar stock al cambiar producto
    $('#tabla-productos').on('change', '.select-producto', function () {
        const seleccionado = $(this).val();
        let repetido = false;
        $('.select-producto').not(this).each(function () {
            if ($(this).val() === seleccionado) repetido = true;
        });

        if (repetido) {
            alert('⚠️ Ese producto ya fue agregado.');
            $(this).val(null).trigger('change');
        } else {
            actualizarStock($(this));
        }
    });

    // Confirmación antes de actualizar
    $('#btnActualizar').on('click', function (e) {
        e.preventDefault();
        Swal.fire({
            title: '¿Actualizar traspaso?',
            text: 'Verifica que los productos y cantidades sean correctos.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, actualizar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $('form').submit();
            }
        });
    });
});
</script>
@endpush
@endsection

