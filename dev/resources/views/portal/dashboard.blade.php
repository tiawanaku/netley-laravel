@extends('layouts.portal')

@section('page_title', 'Mis Procesos')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Bienvenido(a), {{ $cliente->nombre }}</h3>
        </div>
        <div class="card-body">
            @forelse ($procesos as $proceso)
                <div class="mb-2 pb-2 border-bottom">
                    <strong>{{ $proceso->tipo_proceso }}</strong>
                    — Estado: {{ $proceso->estado->label() }}
                    @if ($proceso->abogado)
                        — Abogado: {{ $proceso->abogado->nombre }} {{ $proceso->abogado->apellidos }}
                    @endif
                </div>
            @empty
                <p class="text-muted mb-0">Todavía no tienes procesos registrados.</p>
            @endforelse
        </div>
    </div>
@stop
