@extends('layouts.staff')

@section('page_title', 'Caso: '.$proceso->tipo_proceso)

@section('content')
    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Datos del caso</h3></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Cliente</dt>
                        <dd class="col-7">{{ $proceso->cliente->nombre }} {{ $proceso->cliente->apellidos }}</dd>
                        <dt class="col-5">Teléfono</dt>
                        <dd class="col-7">{{ $proceso->cliente->telefono }}</dd>
                        <dt class="col-5">Materia legal</dt>
                        <dd class="col-7">{{ $proceso->materiaLegal?->nombre ?? '—' }}</dd>
                        <dt class="col-5">Tipo de proceso</dt>
                        <dd class="col-7">{{ $proceso->tipo_proceso }}</dd>
                        <dt class="col-5">Abogado</dt>
                        <dd class="col-7">{{ $proceso->abogado?->nombre }} {{ $proceso->abogado?->apellidos }}</dd>
                        <dt class="col-5">Estado</dt>
                        <dd class="col-7"><span class="badge text-bg-info">{{ $proceso->estado->label() }}</span></dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Informar etapa del proceso</h3></div>
                <div class="card-body">
                    <p class="text-muted">
                        Lo que registres aquí lo verá el administrador y el cliente en su portal.
                    </p>
                    <form method="POST" action="{{ route('staff.procesos.etapa.store', $proceso) }}" class="row g-2">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label">Etapa</label>
                            <input type="text" name="etapa" class="form-control @error('etapa') is-invalid @enderror"
                                placeholder="Ej: Demanda presentada" required>
                            @error('etapa') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Comentario (opcional)</label>
                            <input type="text" name="comentario" class="form-control @error('comentario') is-invalid @enderror">
                            @error('comentario') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Registrar etapa</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Historial de etapas</h3></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Fecha</th><th>Etapa</th><th>Comentario</th><th>Registrado por</th></tr></thead>
                        <tbody>
                            @forelse ($proceso->etapas->sortByDesc('created_at') as $etapa)
                                <tr>
                                    <td>{{ $etapa->created_at->format('d/m/Y H:i') }}</td>
                                    <td><span class="badge text-bg-primary">{{ $etapa->etapa }}</span></td>
                                    <td>{{ $etapa->comentario ?? '—' }}</td>
                                    <td>{{ $etapa->personal?->nombre }} {{ $etapa->personal?->apellidos }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Sin etapas registradas todavía.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
