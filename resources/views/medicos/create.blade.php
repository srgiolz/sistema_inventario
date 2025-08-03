@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Registrar Médico</h2>
    <form action="{{ route('medicos.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="codigo_medico">Código Médico:</label>
            <input type="text" name="codigo_medico" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="nombre">Nombre Completo:</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="especialidad">Especialidad:</label>
            <input type="text" name="especialidad" class="form-control">
        </div>

        <div class="form-group">
            <label for="direccion">Dirección:</label>
            <input type="text" name="direccion" class="form-control">
        </div>

        <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="email" name="email" class="form-control">
        </div>

        <div class="form-group">
            <label for="telefono">Teléfono:</label>
            <input type="text" name="telefono" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Guardar Médico</button>
    </form>
</div>
@endsection
