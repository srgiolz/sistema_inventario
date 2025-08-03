@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Listado de Diagnósticos</h2>
    <a href="{{ route('diagnosticos.create') }}" class="btn btn-success mb-3">Nuevo Diagnóstico</a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($diagnosticos as $diag)
            <tr>
                <td>{{ $diag->descripcion }}</td>
                <td>
                    <form action="{{ route('diagnosticos.destroy', $diag->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button onclick="return confirm('¿Eliminar diagnóstico?')" class="btn btn-danger btn-sm">Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
