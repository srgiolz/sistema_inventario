@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Registrar traspaso entre sucursales</h2>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('traspasos.store') }}" method="POST">
        @csrf

        <!-- Sucursal Origen y Destino -->
        <div class="row mb-3">
            <div class="col">
                <label>De sucursal (origen)</label>
                <select name="de_sucursal" id="de_sucursal" class="form-control" required>
                    <option value="">-- Seleccionar --</option>
                    @foreach($sucursales as $s)
                        <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col">
                <label>A sucursal (destino)</label>
                <select name="a_sucursal" class="form-control" required>
                    <option value="">-- Seleccionar --</option>
                    @foreach($sucursales as $s)
                        <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Fecha y Observación -->
        <div class="mb-3">
            <label>Fecha</label>
            <input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}" required>
        </div>

        <div class="mb-3">
            <label>Observación (opcional)</label>
            <textarea name="observacion" class="form-control"></textarea>
        </div>

        <!-- Tabla de productos -->
        <h5>Productos a traspasar</h5>
        <table class="table table-bordered align-middle" id="tabla-productos">
            <thead class="table-light">
                <tr>
                    <th style="width: 40%">Producto</th>
                    <th style="width: 15%">Stock Disponible</th>
                    <th style="width: 15%">Cantidad a enviar</th>
                    <th style="width: 10%">Acción</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <button type="button" id="agregar-producto" class="btn btn-secondary mb-3">+ Agregar producto</button>

        <br>
        <button type="submit" class="btn btn-primary">Registrar traspaso</button>
        <a href="{{ route('traspasos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
let index = 0;

function crearFilaProducto() {
    const sucursalId = $('#de_sucursal').val();

    if (!sucursalId) {
        alert('Por favor seleccioná primero la sucursal origen.');
        return;
    }

    const fila = `
        <tr>
            <td>
                <select name="productos[${index}][id_producto]" class="form-control select-producto" required></select>
            </td>
            <td>
                <input type="text" class="form-control stock" readonly>
            </td>
            <td>
                <input type="number" name="productos[${index}][cantidad]" class="form-control cantidad" min="1" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm eliminar">🗑</button>
            </td>
        </tr>`;

    $('#tabla-productos tbody').append(fila);
    inicializarSelect2(sucursalId);
    index++;
}

function inicializarSelect2(sucursalId) {
    $('.select-producto').last().select2({
        placeholder: 'Buscar producto...',
        width: '100%',
        ajax: {
            url: `/api/productos-por-sucursal/${sucursalId}`,
            dataType: 'json',
            delay: 250,
            processResults: data => ({
                results: data
            })
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
            .then(data => {
                fila.find('.stock').val(data.stock);
            });
    } else {
        fila.find('.stock').val('');
    }
}

$(document).ready(function () {
    $('#agregar-producto').click(() => crearFilaProducto());

    $('#tabla-productos').on('click', '.eliminar', function () {
        $(this).closest('tr').remove();
    });

    $('#tabla-productos').on('change', '.select-producto', function () {
        actualizarStock($(this));
    });

    $('form').on('submit', function (e) {
        let valido = true;
        $('#tabla-productos tbody tr').each(function () {
            const stock = parseInt($(this).find('.stock').val()) || 0;
            const cantidad = parseInt($(this).find('.cantidad').val()) || 0;
            if (cantidad > stock) {
                alert('No puedes traspasar más de lo disponible.');
                valido = false;
                return false;
            }
        });
        if (!valido) e.preventDefault();
    });
});
</script>
@endpush
@endsection

