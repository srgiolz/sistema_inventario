@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3"><i class="bi bi-pencil-square text-primary"></i> Editar <span class="fw-bold">Salida</span> #{{ $salida->id }}</h4>

    <form id="form-salida" action="{{ route('salidas.update', $salida->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Fecha, Sucursal, Tipo, Motivo --}}
        <div class="row mb-3">
            <div class="col-md-3">
                <label for="fecha">Fecha</label>
                <input type="date" name="fecha" class="form-control" value="{{ $salida->fecha }}" required>
            </div>
            <div class="col-md-4">
                <label for="id_sucursal">Sucursal</label>
                <select name="id_sucursal" class="form-control" required>
                    @foreach($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}" {{ $salida->id_sucursal == $sucursal->id ? 'selected' : '' }}>
                            {{ $sucursal->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="tipo">Tipo</label>
                <input type="text" name="tipo" class="form-control" value="{{ $salida->tipo }}" required>
            </div>
            <div class="col-md-4 mt-2">
                <label for="motivo">Motivo</label>
                <input type="text" name="motivo" class="form-control" value="{{ $salida->motivo }}" required>
            </div>
        </div>

        {{-- Observación --}}
        <div class="mb-3">
            <label>Observación</label>
            <textarea name="observacion" class="form-control">{{ $salida->observacion }}</textarea>
        </div>

        {{-- Tabla de productos --}}
        <table class="table table-bordered" id="tabla-productos">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($salida->detalles as $i => $detalle)
                    <tr>
                        <td>
                            <select name="productos[{{ $i }}][id_producto]" class="form-control select-producto" required>
                                <option value="">-- Seleccionar --</option>
                                @foreach($productos as $producto)
                                    <option value="{{ $producto->id }}" {{ $detalle->id_producto == $producto->id ? 'selected' : '' }}>
                                        {{ $producto->item_codigo }} - {{ $producto->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" name="productos[{{ $i }}][cantidad]" class="form-control" min="1" value="{{ $detalle->cantidad }}" required>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm eliminar-fila">Eliminar</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button type="button" class="btn btn-secondary" id="agregar-fila">Agregar producto</button>
        <button type="submit" class="btn btn-primary">Actualizar salida</button>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    let filaIndex = {{ count($salida->detalles) }};

    function activarSelect2() {
        $('.select-producto').select2({ width: '100%', placeholder: "-- Seleccionar producto --" });
    }
    document.addEventListener('DOMContentLoaded', function () {
        activarSelect2();
    });

    document.getElementById('agregar-fila').addEventListener('click', function () {
        const tbody = document.querySelector('#tabla-productos tbody');
        const nuevaFila = document.createElement('tr');
        nuevaFila.innerHTML = `
            <td>
                <select name="productos[${filaIndex}][id_producto]" class="form-control select-producto" required>
                    <option value="">-- Seleccionar --</option>
                    @foreach($productos as $producto)
                        <option value="{{ $producto->id }}">{{ $producto->item_codigo }} - {{ $producto->descripcion }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="number" name="productos[${filaIndex}][cantidad]" class="form-control" min="1" required></td>
            <td><button type="button" class="btn btn-danger btn-sm eliminar-fila">Eliminar</button></td>
        `;
        tbody.appendChild(nuevaFila);
        filaIndex++;
        activarSelect2();
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('eliminar-fila')) {
            e.target.closest('tr').remove();
        }
    });
</script>
@endpush
