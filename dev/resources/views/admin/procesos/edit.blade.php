@extends('layouts.admin')

@section('page_title', 'Editar caso')

@section('content')
    <div class="card" style="max-width: 700px;">
        <div class="card-header">
            <h3 class="card-title">Editar caso</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.procesos.update', $proceso) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Materia legal</label>
                    <select name="materia_legal_id" class="form-select @error('materia_legal_id') is-invalid @enderror">
                        @foreach ($materiasLegales as $materia)
                            <option value="{{ $materia->id }}" @selected(old('materia_legal_id', $proceso->materia_legal_id) == $materia->id)>{{ $materia->nombre }}</option>
                        @endforeach
                    </select>
                    @error('materia_legal_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipo de proceso</label>
                    <input type="text" name="tipo_proceso" class="form-control @error('tipo_proceso') is-invalid @enderror" value="{{ old('tipo_proceso', $proceso->tipo_proceso) }}">
                    @error('tipo_proceso') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Tiempo estimado (meses)</label>
                    <input type="number" min="1" name="tiempo_proceso_meses" class="form-control @error('tiempo_proceso_meses') is-invalid @enderror" value="{{ old('tiempo_proceso_meses', $proceso->tiempo_proceso_meses) }}">
                    @error('tiempo_proceso_meses') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Abogado asignado</label>
                    <select name="abogado_id" class="form-select @error('abogado_id') is-invalid @enderror">
                        @foreach ($abogados as $abogado)
                            <option value="{{ $abogado->id }}" @selected(old('abogado_id', $proceso->abogado_id) == $abogado->id)>{{ $abogado->nombre }} {{ $abogado->apellidos }}</option>
                        @endforeach
                    </select>
                    @error('abogado_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select @error('estado') is-invalid @enderror">
                        <option value="activo" @selected(old('estado', $proceso->estado->value) === 'activo')>Activo</option>
                        <option value="cerrado" @selected(old('estado', $proceso->estado->value) === 'cerrado')>Cerrado</option>
                        <option value="archivado" @selected(old('estado', $proceso->estado->value) === 'archivado')>Archivado</option>
                    </select>
                    @error('estado') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="{{ route('admin.procesos.show', $proceso) }}" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@stop
