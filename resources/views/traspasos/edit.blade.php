@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 1100px;">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="bi bi-pencil-square text-warning me-2"></i>
            <span class="fw-semibold">Editar traspaso #{{ $traspaso->id }}</span>
        </h4>
        <a href="{{ route('traspasos.index') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-clock-history me-1"></i> Historial de Traspasos
        </a>
    </div>

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
                <div class="row g-3 mb-2">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">De sucursal (origen)</label>
                        <input type="text" class="form-control" value="{{ $traspaso->sucursalOrigen->nombre }}" disabled>
                        <input type="hidden" id="de_sucursal" value="{{ $traspaso->sucursal_origen_id }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">A sucursal (destino)</label>
                        <select name="a_sucursal" id="a_sucursal" class="form-select" required>
                            <option value="">— Seleccionar —</option>
                            @foreach($sucursales as $s)
                                <option value="{{ $s->id }}" 
                                    {{ $traspaso->sucursal_destino_id == $s->id ? 'selected' : '' }}>
                                    {{ $s->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Fecha / Observación --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Fecha</label>
                        <input type="date" name="fecha" class="form-control" 
                               value="{{ \Carbon\Carbon::parse($traspaso->fecha)->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Observación (opcional)</label>
                        <textarea name="observacion" class="form-control" rows="2">{{ $traspaso->observacion }}</textarea>
                    </div>
                </div>

                {{-- ===== Productos a traspasar ===== --}}
                <h6 class="fw-semibold text-body mb-2">Productos a traspasar</h6>
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
                            @foreach($traspaso->detalles as $i => $detalle)
                                <tr>
                                    <td>
                                        <select name="productos[{{ $i }}][producto_id]" 
                                                class="form-control select-producto" required
                                                data-selected="{{ $detalle->producto->id }}">
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
                                               min="1" value="{{ $detalle->cantidad }}" required>
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

                <button type="button" id="agregar-producto" class="btn btn-outline-secondary btn-sm mb-3">
                    <i class="bi bi-plus-circle me-1"></i> Agregar producto
                </button>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('traspasos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check2-circle me-1"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Scripts --}}
@push('scripts')
<script>
let index = {{ $traspaso->detalles->count() }};
const sucursalId = $('#de_sucursal').val();

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
    // Inicializar todos los productos cargados
    $('.select-producto').each(function () {
        inicializarSelect2(this);
        actualizarStock($(this));
    });

    // Agregar nueva fila
    $('#agregar-producto').on('click', function () {
        const fila = `
            <tr>
                <td>
                    <select name="productos[${index}][producto_id]" class="form-control select-producto" required></select>
                </td>
                <td class="text-center"><input type="text" class="form-control stock text-center" readonly></td>
                <td class="text-center">
                    <input type="number" name="productos[${index}][cantidad]" class="form-control cantidad text-center" min="1" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm eliminar"><i class="bi bi-trash"></i></button>
                </td>
            </tr>`;
        $('#tabla-productos tbody').append(fila);
        inicializarSelect2($('.select-producto').last());
        index++;
    });

    // Eliminar fila
    $('#tabla-productos').on('click', '.eliminar', function () {
        $(this).closest('tr').remove();
    });

    // Actualizar stock cuando cambie producto
    $('#tabla-productos').on('change', '.select-producto', function () {
        actualizarStock($(this));
    });
});
</script>
@endpush
@endsection
