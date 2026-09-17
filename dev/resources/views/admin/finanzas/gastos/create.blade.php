@extends('layouts.admin')

@section('page_title', 'Nuevo gasto')

@section('content')
    <div class="card">
        <div class="card-header"><h3 class="card-title">Registrar gasto</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.finanzas.gastos.store') }}" enctype="multipart/form-data" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label class="form-label">Categoría</label>
                    <select name="categoria" class="form-select @error('categoria') is-invalid @enderror">
                        <option value="">Seleccione...</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->value }}" @selected(old('categoria') === $categoria->value)>{{ $categoria->label() }}</option>
                        @endforeach
                    </select>
                    @error('categoria') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Caso relacionado (opcional)</label>
                    <select name="proceso_id" class="form-select @error('proceso_id') is-invalid @enderror">
                        <option value="">— Gasto general del despacho —</option>
                        @foreach ($procesos as $proceso)
                            <option value="{{ $proceso->id }}" @selected(old('proceso_id') == $proceso->id)>
                                {{ $proceso->cliente->nombre }} {{ $proceso->cliente->apellidos }} — {{ $proceso->tipo_proceso }}
                            </option>
                        @endforeach
                    </select>
                    @error('proceso_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    <div class="form-text">Ej: fotocopias del expediente de un caso puntual. Déjalo vacío si es un gasto general (papelería de oficina, etc.).</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fecha</label>
                    <input type="date" name="fecha" class="form-control @error('fecha') is-invalid @enderror" value="{{ old('fecha', now()->toDateString()) }}">
                    @error('fecha') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-8">
                    <label class="form-label">Monto (Bs.)</label>
                    <input type="number" step="0.01" min="0.01" name="monto" class="form-control @error('monto') is-invalid @enderror" value="{{ old('monto') }}">
                    @error('monto') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" rows="2" class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion') }}</textarea>
                    @error('descripcion') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Comprobante (opcional)</label>
                    <input type="file" name="comprobante" class="form-control @error('comprobante') is-invalid @enderror">
                    <div class="form-text">PDF, JPG o PNG — máx. 10 MB.</div>
                    @error('comprobante') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Registrar gasto</button>
                    <a href="{{ route('admin.finanzas.gastos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@stop
