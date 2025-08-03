@extends('layouts.app')
@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="container">

    {{-- 🔔 Mensajes flash --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <h4 class="mb-3"><i class="bi bi-box-arrow-in-down text-primary"></i> Historial de <span class="fw-bold">Entradas</span></h4>

    <a href="{{ route('entradas.create') }}" class="btn btn-primary mb-3">+ Nueva Entrada</a>

    @foreach($entradas as $entrada)
        <div class="card mb-4 shadow-sm border-start border-primary border-4">
            <div class="card-body">
                <h5 class="card-title mb-1">Entrada #{{ $entrada->id }}</h5>
                <div class="mb-2 small text-muted">
                    <strong>Sucursal:</strong> {{ $entrada->sucursal->nombre }} |
                    <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($entrada->fecha)->format('d/m/Y') }} |
                    <strong>Tipo:</strong> {{ $entrada->tipo ?? 'No especificado' }}
                </div>

                @if ($entrada->observacion)
                    <p class="mb-3"><strong>Observación:</strong> {{ $entrada->observacion }}</p>
                @endif

                <table class="table table-bordered table-sm mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entrada->detalles as $detalle)
                            <tr>
                                <td>{{ $detalle->producto->item_codigo }} - {{ $detalle->producto->descripcion }}</td>
                                <td class="text-center">{{ $detalle->cantidad }}</td>
                                <td class="text-center">{{ number_format($detalle->precio_unitario, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Botón Editar (solo si es del mismo día y no es una reversión) --}}
                @if (\Carbon\Carbon::parse($entrada->fecha)->isToday() && !in_array($entrada->id, $idsReversadas) && !Str::startsWith($entrada->observacion, 'Reversión de entrada'))
                    <a href="{{ route('entradas.edit', $entrada->id) }}" class="btn btn-outline-primary btn-sm me-2 mt-3">
                        <i class="bi bi-pencil-square"></i> Editar Entrada
                    </a>
                @endif
                {{-- Botón Generar PDF --}}
<a href="{{ route('entradas.pdf', $entrada->id) }}" class="btn btn-outline-secondary btn-sm mt-3 me-2" target="_blank">
    <i class="bi bi-file-earmark-pdf"></i> Ver PDF
</a>

                {{-- Botón Reversar (solo si no fue reversada ni es una reversa) --}}
                @if (!in_array($entrada->id, $idsReversadas) && !Str::startsWith($entrada->observacion, 'Reversión de entrada'))
                    <form action="{{ route('entradas.reversar', $entrada->id) }}" method="POST" class="mt-2">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Deseas reversar esta entrada? Esta acción no puede deshacerse.')">
                            <i class="bi bi-arrow-counterclockwise"></i> Reversar Entrada
                        </button>
                    </form>
                @endif

            </div>
        </div>
    @endforeach
</div>
@endsection


