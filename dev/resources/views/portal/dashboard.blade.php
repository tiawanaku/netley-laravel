@extends('layouts.portal')

@section('page_title', 'Mis Procesos')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Bienvenido(a), {{ $cliente->nombre }}</h3>
        </div>
        <div class="card-body">
            @forelse ($procesos as $proceso)
                @php $etapaActual = $proceso->etapaActual(); @endphp
                <div class="mb-2 pb-2 border-bottom">
                    <strong>{{ $proceso->tipo_proceso }}</strong>
                    — Estado: {{ $proceso->estado->label() }}
                    @if ($proceso->abogado)
                        — Abogado: {{ $proceso->abogado->nombre }} {{ $proceso->abogado->apellidos }}
                    @endif
                    <br>
                    <span class="text-muted">Etapa actual:</span>
                    @if ($etapaActual)
                        <span class="badge text-bg-primary">{{ $etapaActual->etapa }}</span>
                        <small class="text-muted">({{ $etapaActual->created_at->format('d/m/Y') }})</small>
                    @else
                        <span class="text-muted">Aún sin informar</span>
                    @endif
                </div>
            @empty
                <p class="text-muted mb-0">Todavía no tienes procesos registrados.</p>
            @endforelse
        </div>
    </div>
@stop
