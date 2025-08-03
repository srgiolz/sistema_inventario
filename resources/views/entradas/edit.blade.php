@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3"><i class="bi bi-pencil-square text-primary"></i> Editar <span class="fw-bold">Entrada</span> #{{ $entrada->id }}</h4>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="form-editar-entrada" action="{{ route('entradas.update', $entrada->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Fecha, Sucursal y Tipo --}}
        <div class="row mb-3">
            <div class="col-md-3">
                <label for="fecha">Fecha</label>
                <input type="date" name="fecha" class="form-control" value="{{ $entrada->fecha }}" required>
            </div>
            <div class="col-md-4">
                <label for="id_sucursal">Sucursal destino</label>
                <select name="id_sucursal" class="form-control" required>
                    <option value="">-- Seleccionar --</option>
                    @foreach($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}" {{ $sucursal->id == $entrada->id_sucursal ? 'selected' : '' }}>
                            {{ $sucursal->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="tipo">Tipo de entrada</label>
                <select name="tipo" class="form-control" required>
                    <option value="">-- Seleccionar --</option>
                    @foreach(['Compra', 'Devolución', 'Ajuste de inventario', 'Donación', 'Producción', 'Regularización', 'Otro'] as $opcion)
                        <option value="{{ $opcion }}" {{ $entrada->tipo == $opcion ? 'selected' : '' }}>{{ $opcion }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Observación --}}
        <div class="mb-3">
            <label>Observación</label>
            <textarea name="observacion" class="form-control">{{ $entrada->observacion }}</textarea>
        </div>

        {{-- Tabla de productos --}}
        <table class="table table-bordered" id="tabla-productos">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio unitario</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entrada->detalles as $index => $detalle)
                    <tr>
                        <td>
                            <select name="productos[{{ $index }}][id_producto]" class="form-control" required>
                                <option value="">-- Seleccionar --</option>
                                @foreach($productos as $producto)
                                    <option value="{{ $producto->id }}" {{ $producto->id == $detalle->id_producto ? 'selected' : '' }}>
                                        {{ $producto->item_codigo }} - {{ $producto->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" name="productos[{{ $index }}][cantidad]" class="form-control" min="1" value="{{ $detalle->cantidad }}" required></td>
                        <td><input type="number" step="0.01" name="productos[{{ $index }}][precio_unitario]" class="form-control" value="{{ $detalle->precio_unitario }}"></td>
                        <td><button type="button" class="btn btn-danger btn-sm eliminar-fila">Eliminar</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button type="button" class="btn btn-secondary" id="agregar-fila">Agregar producto</button>
        <button type="submit" class="btn btn-success">Actualizar entrada</button>
        <a href="{{ route('entradas.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection

@push('scripts')
<!-- Select2 CSS y JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let filaIndex = {{ count($entrada->detalles) }};

    function activarSelect2() {
        $('select[name^="productos"]').select2({
            width: '100%',
            placeholder: "-- Seleccionar producto --"
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        activarSelect2();

        document.getElementById('agregar-fila').addEventListener('click', function () {
            const tbody = document.querySelector('#tabla-productos tbody');
            const nuevaFila = document.createElement('tr');

            nuevaFila.innerHTML = `
                <td>
                    <select name="productos[${filaIndex}][id_producto]" class="form-control" required>
                        <option value="">-- Seleccionar --</option>
                        @foreach($productos as $producto)
                            <option value="{{ $producto->id }}">{{ $producto->item_codigo }} - {{ $producto->descripcion }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="number" name="productos[${filaIndex}][cantidad]" class="form-control" min="1" required></td>
                <td><input type="number" step="0.01" name="productos[${filaIndex}][precio_unitario]" class="form-control"></td>
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

        // Confirmación al actualizar
        document.getElementById('form-editar-entrada').addEventListener('submit', function (e) {
            e.preventDefault();

            Swal.fire({
                title: '¿Actualizar entrada?',
                text: 'Esto modificará la información y el inventario.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    e.target.submit();
                }
            });
        });
    });
</script>
@endpush
