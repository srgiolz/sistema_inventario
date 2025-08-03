@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Listado de Clientes</h2>

    <a href="{{ route('clientes.create') }}" class="btn btn-success mb-3">Nuevo Cliente</a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nombre Completo</th>
                <th>CI/NIT</th>
                <th>Tipo</th>
                <th>Teléfono</th>
                <th>Ciudad</th>
                <th>Dirección</th>
                <th>Médico</th>
                <th>Diagnóstico</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $cliente)
            <tr>
                <td>{{ $cliente->nombre }} {{ $cliente->apellido }}</td>
                <td>{{ $cliente->ci_nit }}</td>
                <td>{{ ucfirst($cliente->tipo_cliente) }}</td>
                <td>{{ $cliente->telefono }}</td>
                <td>{{ $cliente->ciudad }}</td>
                <td>{{ $cliente->direccion }}</td>
                <td>{{ optional($cliente->medico)->nombre ?? '-' }}</td>
                <td>{{ optional($cliente->diagnostico)->descripcion ?? '-' }}</td>
                <td>
                    <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn btn-sm btn-warning">Editar</a>
                    <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button onclick="return confirm('¿Seguro que deseas eliminar?')" class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
