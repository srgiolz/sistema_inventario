@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Registrar Diagnóstico</h2>
    <form action="{{ route('diagnosticos.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="descripcion">Descripción:</label>
            <input type="text" name="descripcion" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
</div>
@endsection
