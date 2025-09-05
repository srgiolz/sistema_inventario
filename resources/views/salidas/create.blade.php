@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Registrar nueva salida de productos</h2>

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

    <form id="form-salida" action="{{ route('salidas.store') }}" method="POST">
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
            <div class="col-md-4">
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
            <div class="col-md-6 mt-2">
                <label for="motivo">Motivo</label>
                <input type="text" name="motivo" class="form-control" required>
            </div>
        </div>

        {{-- 📝 Observación --}}
        <div class="mb-3">
            <label>Observación</label>
            <textarea name="observacion" class="form-control"></textarea>
        </div>

        {{-- 📦 Tabla de productos --}}
        <table class="table table-bordered" id="tabla-productos">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th width="120">Cantidad</th>
                    <th width="100">Acción</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select name="productos[0][producto_id]" class="form-control" required>
                            <option value="">-- Seleccionar --</option>
                            @foreach($productos as $producto)
                                <option value="{{ $producto->id }}">{{ $producto->codigo_item }} - {{ $producto->descripcion }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" name="productos[0][cantidad]" class="form-control" min="1" required></td>
                    <td><button type="button" class="btn btn-danger btn-sm eliminar-fila">Eliminar</button></td>
                </tr>
            </tbody>
        </table>

        <button type="button" class="btn btn-secondary" id="agregar-fila">Agregar producto</button>
        <button type="submit" class="btn btn-primary">Registrar salida</button>
    </form>
</div>
@endsection

@push('scripts')
<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    let filaIndex = 1;

    function activarSelect2() {
        $('select[name^="productos"]').select2({
            width: '100%',
            placeholder: "-- Seleccionar producto --"
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        activarSelect2();
    });

    document.getElementById('agregar-fila').addEventListener('click', function () {
        const tbody = document.querySelector('#tabla-productos tbody');
        const nuevaFila = document.createElement('tr');

        nuevaFila.innerHTML = `
            <td>
                <select name="productos[${filaIndex}][producto_id]" class="form-control" required>
                    <option value="">-- Seleccionar --</option>
                    @foreach($productos as $producto)
                        <option value="{{ $producto->id }}">{{ $producto->codigo_item }} - {{ $producto->descripcion }}</option>
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

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('form-salida');

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            let productos = document.querySelectorAll('select[name^="productos"]');
            let cantidades = document.querySelectorAll('input[name*="[cantidad]"]');
            let ids = [];
            let duplicado = false;
            let vacio = false;

            productos.forEach((select, i) => {
                let val = select.value;
                if (!val) vacio = true;
                if (ids.includes(val)) duplicado = true;
                ids.push(val);

                let cantidad = cantidades[i]?.value;
                if (!cantidad || cantidad <= 0) vacio = true;
            });

            if (vacio) {
                Swal.fire('Error', 'Todos los productos y cantidades deben estar completos y válidos.', 'error');
                return;
            }

            if (duplicado) {
                Swal.fire('Error', 'No se permiten productos duplicados.', 'error');
                return;
            }

            Swal.fire({
                title: '¿Registrar salida?',
                text: 'Verifica que los productos y cantidades sean correctos.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, registrar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush

