@extends('layouts.admin')

@section('page_title', 'Editar Cliente')

@section('content')
    <div class="card" style="max-width: 640px;">
        <div class="card-header">
            <h3 class="card-title">Editar datos de {{ $cliente->nombre }}</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.clientes.update', $cliente) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $cliente->nombre) }}">
                    @error('nombre') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Apellidos</label>
                    <input type="text" name="apellidos" class="form-control @error('apellidos') is-invalid @enderror" value="{{ old('apellidos', $cliente->apellidos) }}">
                    @error('apellidos') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">CI</label>
                    <input type="text" name="ci" class="form-control @error('ci') is-invalid @enderror" value="{{ old('ci', $cliente->ci) }}">
                    @error('ci') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror" value="{{ old('telefono', $cliente->telefono) }}">
                    @error('telefono') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">WhatsApp</label>
                    <input type="text" name="whatsapp" class="form-control @error('whatsapp') is-invalid @enderror" value="{{ old('whatsapp', $cliente->whatsapp) }}">
                    @error('whatsapp') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="{{ route('admin.clientes.show', $cliente) }}" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@stop
