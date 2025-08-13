@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Editar Producto</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('productos.update', $producto->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            @php
                $campos = [
                    'codigo_item' => 'Código',
                    'cod_barra' => 'Código de Barra',
                    'descripcion' => 'Descripción',
                    'linea' => 'Línea',
                    'familia' => 'Familia',
                    'unidad_medida' => 'Unidad de Medida',
                    'talla' => 'Talla',
                    'modelo' => 'Modelo',
                    'puntera' => 'Puntera',
                    'color' => 'Color',
                    'compresion' => 'Compresión',
                    'categoria' => 'Categoría',
                ];
            @endphp

            @foreach($campos as $name => $label)
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ $label }}</label>
                    <input type="text" name="{{ $name }}" class="form-control"
                        value="{{ old($name, $producto->$name) }}"
                        oninput="this.value = this.value.toUpperCase();">
                </div>
            @endforeach

            <div class="col-md-6 mb-3">
                <label class="form-label">Precio de Costo</label>
                <input type="number" step="0.01" name="precio_costo" class="form-control"
                    value="{{ old('precio_costo', $producto->precio_costo) }}">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Precio de Venta</label>
                <input type="number" step="0.01" name="precio_venta" class="form-control"
                    value="{{ old('precio_venta', $producto->precio_venta) }}">
            </div>
        </div>

        <button type="submit" class="btn btn-success">Actualizar</button>
        <a href="{{ route('productos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
