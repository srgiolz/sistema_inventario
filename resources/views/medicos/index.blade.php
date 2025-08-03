@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Listado de Médicos</h2>

    <a href="{{ route('medicos.create') }}" class="btn btn-success mb-3">Nuevo Médico</a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Especialidad</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Dirección</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($medicos as $medico)
            <tr>
                <td>{{ $medico->codigo_medico }}</td>
                <td>{{ $medico->nombre }}</td>
                <td>{{ $medico->especialidad }}</td>
                <td>{{ $medico->telefono }}</td>
                <td>{{ $medico->email }}</td>
                <td>{{ $medico->direccion }}</td>
                <td>
                    <form action="{{ route('medicos.destroy', $medico->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button onclick="return confirm('¿Eliminar médico?')" class="btn btn-danger btn-sm">Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
