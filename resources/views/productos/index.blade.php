@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Listado de Productos</h2>

    <a href="{{ route('productos.create') }}" class="btn btn-primary mb-3">+ Nuevo Producto</a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
    <table class="table table-bordered table-hover table-sm">

            <thead class="table-dark text-center">
    <tr>
        <th>ID</th>
        <th>Código</th>
        <th>Descripción</th>
        <th>Línea</th>
        <th>Talla</th>
        <th>Color</th>
        <th>Precio Venta</th>
        <th>Acciones</th>
    </tr>
</thead>
<tbody>
    @foreach($productos as $p)
        <tr>
            <td>{{ $p->id }}</td>
            <td>{{ $p->codigo_item }}</td>
            <td>{{ $p->descripcion }}</td>
            <td>{{ $p->linea }}</td>
            <td>{{ $p->talla }}</td>
            <td>{{ $p->color }}</td>
            <td>{{ $p->precio_venta }}</td>
            <td class="text-center">
                <div class="d-flex justify-content-center gap-1">
                    <a href="{{ route('productos.edit', $p->id) }}" class="btn btn-sm btn-warning" title="Editar">✏️</a>
                    <form action="{{ route('productos.destroy', $p->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este producto?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">🗑️</button>
                    </form>
                </div>
            </td>
        </tr>
    @endforeach
</tbody>

        </table>
    </div>
</div>
@endsection
