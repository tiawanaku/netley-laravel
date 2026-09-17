@extends('layouts.admin')

@section('page_title', 'Nuevo recibo')

@section('content')
    <div class="card">
        <div class="card-header"><h3 class="card-title">Registrar recibo manual</h3></div>
        <div class="card-body">
            <p class="text-muted">
                Usa esto para ingresos que no vienen de una cuota o anticipo del plan de pagos
                (esos generan su recibo automáticamente al confirmarlos). El número correlativo
                se asigna solo.
            </p>
            <form method="POST" action="{{ route('admin.finanzas.recibos.store') }}" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label class="form-label">Caso (opcional)</label>
                    <select name="proceso_id" class="form-select @error('proceso_id') is-invalid @enderror">
                        <option value="">— Sin caso asociado —</option>
                        @foreach ($procesos as $proceso)
                            <option value="{{ $proceso->id }}" @selected(old('proceso_id') == $proceso->id)>
                                {{ $proceso->cliente->nombre }} {{ $proceso->cliente->apellidos }} — {{ $proceso->tipo_proceso }}
                            </option>
                        @endforeach
                    </select>
                    @error('proceso_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fecha</label>
                    <input type="date" name="fecha" class="form-control @error('fecha') is-invalid @enderror" value="{{ old('fecha', now()->toDateString()) }}">
                    @error('fecha') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Monto (Bs.)</label>
                    <input type="number" step="0.01" min="0.01" name="monto" class="form-control @error('monto') is-invalid @enderror" value="{{ old('monto') }}">
                    @error('monto') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-8">
                    <label class="form-label">Concepto</label>
                    <input type="text" name="concepto" class="form-control @error('concepto') is-invalid @enderror" placeholder="Ej: Honorarios por consulta puntual" value="{{ old('concepto') }}">
                    @error('concepto') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Registrar recibo</button>
                    <a href="{{ route('admin.finanzas.recibos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@stop
