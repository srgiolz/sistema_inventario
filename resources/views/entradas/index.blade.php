@extends('layouts.app')
@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="container">

    <h4 class="mb-3">
        <i class="bi bi-box-arrow-in-down text-primary"></i> 
        Historial de <span class="fw-bold">Entradas</span>
    </h4>

    <a href="{{ route('entradas.create') }}" class="btn btn-primary mb-4 shadow-sm">
        <i class="bi bi-plus-circle me-1"></i> Nueva Entrada
    </a>

    @foreach($entradas as $entrada)
        <div class="card mb-4 border-0 shadow-sm rounded-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                <h5 class="mb-0">
                    <i class="bi bi-box-seam text-primary"></i> Entrada #{{ $entrada->id }}
                </h5>
                <small class="text-muted">
                    <strong>Sucursal:</strong> {{ $entrada->sucursal->nombre }} |
                    <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($entrada->fecha)->format('d/m/Y') }} |
                    <strong>Tipo:</strong> {{ $entrada->tipo ?? 'No especificado' }}
                </small>
            </div>

            <div class="card-body">
                @if ($entrada->observacion)
                    <div class="p-2 mb-3 bg-light rounded border-start border-3 border-primary">
                        <i class="bi bi-journal-text text-primary me-2"></i>
                        <strong>Observación:</strong> {{ $entrada->observacion }}
                    </div>
                @endif

                <table class="table table-hover table-sm align-middle">
                    <thead class="table-primary text-center">
                        <tr>
                            <th>Producto</th>
                            <th width="120">Cantidad</th>
                            <th width="150">Precio Unitario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entrada->detalles as $detalle)
                            <tr>
                                <td>{{ $detalle->producto->codigo_item }} - {{ $detalle->producto->descripcion }}</td>
                                <td class="text-center fw-bold">{{ $detalle->cantidad }}</td>
                                <td class="text-center">{{ number_format($detalle->precio_unitario, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Botones de acción --}}
                <div class="mt-3 d-flex flex-wrap gap-2">
                    {{-- Editar --}}
                    @if (\Carbon\Carbon::parse($entrada->fecha)->isToday() && !in_array($entrada->id, $idsReversadas) && !Str::startsWith($entrada->observacion, 'Reversión de entrada'))
                        <a href="{{ route('entradas.edit', $entrada->id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-pencil-square"></i> Editar
                        </a>
                    @endif

                    {{-- PDF --}}
                    <a href="{{ route('entradas.pdf', $entrada->id) }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                        <i class="bi bi-file-earmark-pdf"></i> Ver PDF
                    </a>

                    {{-- Reversar --}}
                    @if (!in_array($entrada->id, $idsReversadas) && !Str::startsWith($entrada->observacion, 'Reversión de entrada'))
                        <form action="{{ route('entradas.reversar', $entrada->id) }}" method="POST" onsubmit="return confirm('¿Deseas reversar esta entrada? Esta acción no puede deshacerse.')" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-arrow-counterclockwise"></i> Reversar
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection

