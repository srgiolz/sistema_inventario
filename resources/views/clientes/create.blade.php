@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Registrar Cliente</h2>

    <form action="{{ route('clientes.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="tipo_cliente">Tipo de Cliente:</label>
            <select name="tipo_cliente" class="form-control" required>
                <option value="particular">Particular</option>
                <option value="paciente">Paciente</option>
            </select>
        </div>

        <div class="form-group">
            <label for="ci_nit">CI o NIT:</label>
            <input type="text" name="ci_nit" class="form-control">
        </div>

        <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="apellido">Apellido:</label>
            <input type="text" name="apellido" class="form-control">
        </div>

        <div class="form-group">
            <label for="sexo">Sexo:</label>
            <input type="text" name="sexo" class="form-control">
        </div>

        <div class="form-group">
            <label for="ciudad">Ciudad:</label>
            <input type="text" name="ciudad" class="form-control">
        </div>

        <div class="form-group">
            <label for="direccion">Dirección:</label>
            <input type="text" name="direccion" class="form-control">
        </div>

        <div class="form-group">
            <label for="telefono">Teléfono:</label>
            <input type="text" name="telefono" class="form-control">
        </div>

        <div class="form-group">
            <label for="id_medico">Médico (si aplica):</label>
            <select name="id_medico" class="form-control">
                <option value="">-- Ninguno --</option>
                @foreach($medicos as $medico)
                    <option value="{{ $medico->id }}">{{ $medico->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="id_diagnostico">Diagnóstico (si aplica):</label>
            <select name="id_diagnostico" class="form-control">
                <option value="">-- Ninguno --</option>
                @foreach($diagnosticos as $diag)
                    <option value="{{ $diag->id }}">{{ $diag->descripcion }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Guardar Cliente</button>
    </form>
</div>
@endsection
