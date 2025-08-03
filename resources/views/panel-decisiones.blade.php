@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">📊 Panel de Decisiones Inteligentes</h2>

<form method="GET" action="{{ route('panel.index') }}" class="row g-3 mb-4">
    <div class="col-md-5">
        <label for="linea" class="form-label">Filtrar por línea:</label>
        <input type="text" name="linea" class="form-control" placeholder="Ej: Ortopedia" value="{{ request('linea') }}">
    </div>

    <div class="col-md-5">
        <label for="producto" class="form-label">Filtrar por producto:</label>
        <input type="text" name="producto" class="form-control" placeholder="Ej: FAJA" value="{{ request('producto') }}">
    </div>

    <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100">🔎 Filtrar</button>
    </div>
</form>


    <div class="mb-4">
    <h5 class="text-success">📦 Stock total en sistema: {{ $stockGeneral }} unidades</h5>
</div>

<h5>🏪 Stock por sucursal</h5>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Sucursal</th>
            <th>Total de unidades</th>
        </tr>
    </thead>
    <tbody>
        @foreach($stockPorSucursal as $item)
        <tr>
            <td>{{ $item->sucursal }}</td>
            <td>{{ $item->total_stock }}</td>
        </tr>
        @endforeach
    </tbody>
</table>


    <h4>✅ Stock total por producto</h4>
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Stock Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stockTotal as $item)
            <tr>
                <td>{{ $item->descripcion }}</td>
                <td>{{ $item->stock_total }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <h4 class="mt-5 text-warning">⚠️ Productos con bajo stock (&lt; 10 unidades)</h4>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Producto</th>
            <th>Stock Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($bajoStock as $item)
        <tr>
            <td>{{ $item->descripcion }}</td>
            <td>{{ $item->stock_total }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<h4 class="mt-5 text-danger">🚨 Productos agotados (stock = 0)</h4>
@if($agotados->isEmpty())
    <p class="text-muted">No hay productos completamente agotados 🎉</p>
@else
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Producto</th>
        </tr>
    </thead>
    <tbody>
        @foreach($agotados as $item)
        <tr>
            <td>{{ $item->descripcion }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif
<h4 class="mt-5 text-primary">🔥 Top 10 productos más vendidos</h4>
<table class="table table-hover">
    <thead>
        <tr>
            <th>Producto</th>
            <th>Total vendido</th>
        </tr>
    </thead>
    <tbody>
        @foreach($masVendidos as $item)
        <tr>
            <td>{{ $item->descripcion }}</td>
            <td>{{ $item->total_vendido }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<h4 class="mt-5 text-info">🔄 Stock por producto y sucursal</h4>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Producto</th>
            <th>Sucursal</th>
            <th>Stock</th>
        </tr>
    </thead>
    <tbody>
        @foreach($stockPorProductoSucursal as $item)
        <tr>
            <td>{{ $item->descripcion }}</td>
            <td>{{ $item->sucursal }}</td>
            <td>{{ $item->cantidad }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<h4 class="mt-5 text-info">🔄 Sugerencias de traspaso entre sucursales</h4>

@if($traspasosSugeridos->isEmpty())
    <p class="text-muted">No hay sugerencias de traspaso en este momento 👍</p>
@else
<table class="table table-striped">
    <thead>
        <tr>
            <th>Producto</th>
            <th>Desde</th>
            <th>Hacia</th>
            <th>Unidades sugeridas</th>
        </tr>
    </thead>
    <tbody>
        @foreach($traspasosSugeridos as $item)
        <tr>
            <td>{{ $item->producto }}</td>
            <td>{{ $item->de }}</td>
            <td>{{ $item->a }}</td>
            <td>{{ $item->sugerido }}</td>
            <td>
                <form method="GET" action="{{ route('traspasos.create') }}">
                    <input type="hidden" name="producto" value="{{ $item->producto }}">
                    <input type="hidden" name="origen" value="{{ $item->de }}">
                    <input type="hidden" name="destino" value="{{ $item->a }}">
                    <input type="hidden" name="cantidad" value="{{ $item->sugerido }}">
                    <button type="submit" class="btn btn-sm btn-primary">➕ Realizar traspaso</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

</div>
@endsection
