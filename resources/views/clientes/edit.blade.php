@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Cliente</h2>

    <form action="{{ route('clientes.update', $cliente->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="tipo_cliente">Tipo de Cliente:</label>
            <select name="tipo_cliente" class="form-control" required>
                <option value="particular" {{ $cliente->tipo_cliente == 'particular' ? 'selected' : '' }}>Particular</option>
                <option value="paciente" {{ $cliente->tipo_cliente == 'paciente' ? 'selected' : '' }}>Paciente</option>
            </select>
        </div>

        <div class="form-group">
            <label for="ci_nit">CI o NIT:</label>
            <input type="text" name="ci_nit" class="form-control" value="{{ $cliente->ci_nit }}">
        </div>

        <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" class="form-control" value="{{ $cliente->nombre }}" required>
        </div>

        <div class="form-group">
            <label for="apellido">Apellido:</label>
            <input type="text" name="apellido" class="form-control" value="{{ $cliente->apellido }}">
        </div>

        <div class="form-group">
            <label for="sexo">Sexo:</label>
            <input type="text" name="sexo" class="form-control" value="{{ $cliente->sexo }}">
        </div>

        <div class="form-group">
            <label for="ciudad">Ciudad:</label>
            <input type="text" name="ciudad" class="form-control" value="{{ $cliente->ciudad }}">
        </div>

        <div class="form-group">
            <label for="direccion">Dirección:</label>
            <input type="text" name="direccion" class="form-control" value="{{ $cliente->direccion }}">
        </div>

        <div class="form-group">
            <label for="telefono">Teléfono:</label>
            <input type="text" name="telefono" class="form-control" value="{{ $cliente->telefono }}">
        </div>

        <div class="form-group">
            <label for="id_medico">Médico:</label>
            <select name="id_medico" class="form-control">
                <option value="">-- Ninguno --</option>
                @foreach($medicos as $medico)
                    <option value="{{ $medico->id }}" {{ $cliente->id_medico == $medico->id ? 'selected' : '' }}>
                        {{ $medico->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="id_diagnostico">Diagnóstico:</label>
            <select name="id_diagnostico" class="form-control">
                <option value="">-- Ninguno --</option>
                @foreach($diagnosticos as $diag)
                    <option value="{{ $diag->id }}" {{ $cliente->id_diagnostico == $diag->id ? 'selected' : '' }}>
                        {{ $diag->descripcion }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Actualizar Cliente</button>
    </form>
</div>
@section('scripts')
<script>
    $(document).ready(function () {
    // Inicializar Select2
    $('.select-producto').select2({
        placeholder: 'Selecciona un producto',
        ajax: {
            url: '/api/productos', // crea una ruta que devuelva productos
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.map(function (item) {
                        return { id: item.id, text: item.nombre };
                    })
                };
            }
        }
    });

    // Cargar stock al seleccionar producto
    $(document).on('change', '.select-producto', function () {
        let select = $(this);
        let productoId = select.val();
        let fila = select.closest('tr');
        let stockTd = fila.find('.stock-disponible');

        if (!productoId) {
            stockTd.text('--');
            return;
        }

        $.ajax({
            url: '/api/productos/' + productoId + '/stock',
            method: 'GET',
            success: function (data) {
                stockTd.text(data.stock);
            },
            error: function () {
                stockTd.text('Error');
            }
        });
    });

    // Agregar nueva fila
    $('#agregarProducto').click(function () {
        let nuevaFila = `
        <tr>
            <td>
                <select name="productos[]" class="form-control select-producto"></select>
            </td>
            <td class="stock-disponible">--</td>
            <td><input type="number" name="cantidades[]" value="1" class="form-control cantidad" min="1" required></td>
            <td><button type="button" class="btn btn-danger btn-sm eliminar-fila">🗑️</button></td>
        </tr>`;

        $('#tablaProductos tbody').append(nuevaFila);

        // Inicializar select2 en el nuevo select
        $('.select-producto').last().select2({
            placeholder: 'Selecciona un producto',
            ajax: {
                url: '/api/productos',
                dataType: 'json',
                processResults: function (data) {
                    return {
                        results: data.map(function (item) {
                            return { id: item.id, text: item.nombre };
                        })
                    };
                }
            }
        });
    });

    // Eliminar fila
    $(document).on('click', '.eliminar-fila', function () {
        $(this).closest('tr').remove();
    });
});

</script>
@endsection

