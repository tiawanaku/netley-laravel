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
                        @if ($proceso->finanza)
                            <dt class="col-5">Iguala (costo total)</dt>
                            <dd class="col-7">Bs. {{ number_format($proceso->finanza->costo, 2) }}</dd>
                            <dt class="col-5">Anticipo</dt>
                            <dd class="col-7">Bs. {{ number_format($proceso->finanza->anticipo, 2) }}</dd>
                            <dt class="col-5">Saldo pendiente</dt>
                            <dd class="col-7">Bs. {{ number_format($proceso->finanza->costo - $proceso->recibos->sum('monto'), 2) }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            @if ($proceso->recibos->isNotEmpty())
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Cobros registrados</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Fecha</th><th>Concepto</th><th class="text-end">Monto</th></tr></thead>
                            <tbody>
                                @foreach ($proceso->recibos->sortByDesc('fecha') as $recibo)
                                    <tr>
                                        <td>{{ $recibo->fecha->format('d/m/Y') }}</td>
                                        <td>{{ $recibo->concepto }}</td>
                                        <td class="text-end">Bs. {{ number_format((float) $recibo->monto, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-muted small">
                        Los cobros se registran desde el panel Admin (Finanzas). Aquí solo se muestran como referencia.
                    </div>
                </div>
            @endif
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

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Ficha del caso</h3></div>
                <div class="card-body">
                    <p class="text-muted">
                        Complete estos datos a medida que el caso avanza. Se guarda un único registro por
                        caso: cada envío actualiza los datos anteriores.
                    </p>
                    @php $ficha = $proceso->ficha; @endphp
                    <form method="POST" action="{{ route('staff.procesos.ficha.update', $proceso) }}" class="row g-2">
                        @csrf
                        @method('PUT')
                        <div class="col-md-6">
                            <label class="form-label">N° de caso / expediente</label>
                            <input type="text" name="numero_caso" class="form-control @error('numero_caso') is-invalid @enderror"
                                value="{{ old('numero_caso', $ficha?->numero_caso) }}" placeholder="Ej: MP 201502022601545">
                            @error('numero_caso') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha de inicio</label>
                            <input type="date" name="fecha_inicio_caso" class="form-control @error('fecha_inicio_caso') is-invalid @enderror"
                                value="{{ old('fecha_inicio_caso', $ficha?->fecha_inicio_caso?->format('Y-m-d')) }}">
                            @error('fecha_inicio_caso') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha de finalización</label>
                            <input type="date" name="fecha_finalizacion" class="form-control @error('fecha_finalizacion') is-invalid @enderror"
                                value="{{ old('fecha_finalizacion', $ficha?->fecha_finalizacion?->format('Y-m-d')) }}">
                            @error('fecha_finalizacion') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Denunciante / demandante</label>
                            <input type="text" name="denunciante" class="form-control @error('denunciante') is-invalid @enderror"
                                value="{{ old('denunciante', $ficha?->denunciante) }}">
                            @error('denunciante') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Denunciado / demandado</label>
                            <input type="text" name="denunciado" class="form-control @error('denunciado') is-invalid @enderror"
                                value="{{ old('denunciado', $ficha?->denunciado) }}">
                            @error('denunciado') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Resultado / estado del caso</label>
                            <textarea name="resultado" rows="3" class="form-control @error('resultado') is-invalid @enderror"
                                placeholder="Ej: Concluido con resolución de rechazo">{{ old('resultado', $ficha?->resultado) }}</textarea>
                            @error('resultado') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Guardar ficha</button>
                            @if ($ficha)
                                <span class="text-muted small ms-2">
                                    Última actualización: {{ $ficha->updated_at->format('d/m/Y H:i') }}
                                    por {{ $ficha->actualizadoPor?->nombre }} {{ $ficha->actualizadoPor?->apellidos }}
                                </span>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Registrar gestión extrajudicial</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('staff.procesos.gestion.store', $proceso) }}" class="row g-2">
                        @csrf
                        <div class="col-md-4">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha" class="form-control @error('fecha') is-invalid @enderror">
                            @error('fecha') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Motivo</label>
                            <input type="text" name="motivo" class="form-control @error('motivo') is-invalid @enderror"
                                placeholder="Ej: Notificaciones">
                            @error('motivo') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha de devolución</label>
                            <input type="date" name="fecha_devolucion" class="form-control @error('fecha_devolucion') is-invalid @enderror">
                            @error('fecha_devolucion') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Registrar gestión</button>
                        </div>
                    </form>
                </div>
                <div class="card-body p-0 border-top">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Fecha</th><th>Motivo</th><th>Devolución</th><th>Registrado por</th></tr></thead>
                        <tbody>
                            @forelse ($proceso->gestionesExtrajudiciales->sortByDesc('created_at') as $gestion)
                                <tr>
                                    <td>{{ $gestion->fecha?->format('d/m/Y') ?? '—' }}</td>
                                    <td>{{ $gestion->motivo ?? '—' }}</td>
                                    <td>{{ $gestion->fecha_devolucion?->format('d/m/Y') ?? '—' }}</td>
                                    <td>{{ $gestion->personal?->nombre }} {{ $gestion->personal?->apellidos }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Sin gestiones registradas todavía.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
