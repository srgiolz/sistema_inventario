@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">➖ Registrar nueva salida</h2>
    <a href="{{ route('salidas.index') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-arrow-left-circle"></i> Volver al historial
    </a>
</div>


    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('salidas.store') }}" method="POST">
        @csrf

        {{-- 📅 Fecha, Sucursal, Tipo, Motivo --}}
        <div class="row mb-3">
            <div class="col-md-3">
                <label for="fecha">Fecha</label>
                <input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-4">
                <label for="sucursal_id">Sucursal origen</label>
                <select name="sucursal_id" class="form-control" required>
                    <option value="">-- Seleccionar --</option>
                    @foreach($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
    <label for="tipo">Tipo de salida</label>
    <select name="tipo" class="form-control" required>
        <option value="">-- Seleccionar --</option>
        <option value="Consumo interno">Consumo interno</option>
        <option value="Producto vencido">Producto vencido</option>
        <option value="Producto dañado">Producto dañado</option>
        <option value="Muestra médica">Muestra médica</option>
        <option value="Ajuste de inventario">Ajuste de inventario</option>
        <option value="Otro">Otro</option>
    </select>
</div>

            <div class="col-md-6 mt-3">
                <label for="motivo">Motivo</label>
                <input type="text" name="motivo" class="form-control" required>
            </div>
        </div>

        {{-- 📝 Observación --}}
        <div class="mb-3">
            <label>Observación (opcional)</label>
            <textarea name="observacion" class="form-control" rows="2"></textarea>
        </div>

        {{-- 📦 Tabla de productos --}}
        <table class="table table-bordered table-sm" id="tabla-productos">
            <thead class="table-light">
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select name="productos[0][producto_id]" class="form-control producto-select" required>
                            <option value="">-- Seleccionar --</option>
                            @foreach($productos as $p)
                                <option value="{{ $p->id }}">{{ $p->codigo_item }} - {{ $p->descripcion }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" name="productos[0][cantidad]" class="form-control" min="1" required></td>
                    <td><button type="button" class="btn btn-danger btn-sm eliminar-fila">Eliminar</button></td>
                </tr>
            </tbody>
        </table>

        <div class="d-flex gap-2 mt-3">
    <button type="button" class="btn btn-secondary btn-sm" id="agregar-fila">+ Agregar producto</button>
    <button type="submit" class="btn btn-primary btn-sm">Registrar salida</button>
</div>

    </form>
</div>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    let filaIndex = 1;

    function activarSelect2() {
        $('.producto-select').select2({
            width: '100%',
            placeholder: "-- Seleccionar producto --"
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        activarSelect2();

        document.getElementById('agregar-fila').addEventListener('click', function () {
            const tbody = document.querySelector('#tabla-productos tbody');
            const fila = document.createElement('tr');

            fila.innerHTML = `
                <td>
                    <select name="productos[${filaIndex}][producto_id]" class="form-control producto-select" required>
                        <option value="">-- Seleccionar --</option>
                        @foreach($productos as $p)
                            <option value="{{ $p->id }}">{{ $p->codigo_item }} - {{ $p->descripcion }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="number" name="productos[${filaIndex}][cantidad]" class="form-control" min="1" required></td>
                <td><button type="button" class="btn btn-danger btn-sm eliminar-fila">Eliminar</button></td>
            `;

            tbody.appendChild(fila);
            activarSelect2();
            filaIndex++;
        });

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('eliminar-fila')) {
                e.target.closest('tr').remove();
            }
        });
    });
</script>
@endpush
