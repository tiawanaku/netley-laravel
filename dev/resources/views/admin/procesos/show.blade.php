@extends('layouts.admin')

@section('page_title', 'Caso: '.$proceso->tipo_proceso)

@section('content')
    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Datos del caso</h3>
                    <a href="{{ route('admin.procesos.edit', $proceso) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Cliente</dt>
                        <dd class="col-7">
                            <a href="{{ route('admin.clientes.show', $proceso->cliente) }}">{{ $proceso->cliente->nombre }} {{ $proceso->cliente->apellidos }}</a>
                        </dd>
                        <dt class="col-5">Materia legal</dt>
                        <dd class="col-7">{{ $proceso->materiaLegal?->nombre ?? '—' }}</dd>
                        <dt class="col-5">Tipo de proceso</dt>
                        <dd class="col-7">{{ $proceso->tipo_proceso }}</dd>
                        <dt class="col-5">Abogado</dt>
                        <dd class="col-7">{{ $proceso->abogado?->nombre }} {{ $proceso->abogado?->apellidos }}</dd>
                        <dt class="col-5">Tiempo estimado</dt>
                        <dd class="col-7">{{ $proceso->tiempo_proceso_meses }} meses</dd>
                        <dt class="col-5">Estado</dt>
                        <dd class="col-7"><span class="badge text-bg-info">{{ $proceso->estado->label() }}</span></dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Etapa del proceso</h3></div>
                <div class="card-body">
                    @php $etapaActual = $proceso->etapaActual(); @endphp
                    @if ($etapaActual)
                        <p class="mb-1">
                            <span class="badge text-bg-primary fs-6">{{ $etapaActual->etapa }}</span>
                        </p>
                        <p class="text-muted mb-0">
                            Informado por {{ $etapaActual->personal?->nombre }} {{ $etapaActual->personal?->apellidos }}
                            el {{ $etapaActual->created_at->format('d/m/Y H:i') }}
                        </p>
                        @if ($etapaActual->comentario)
                            <p class="mb-0 mt-2">{{ $etapaActual->comentario }}</p>
                        @endif
                    @else
                        <p class="text-muted mb-0">El abogado todavía no informó una etapa para este caso.</p>
                    @endif

                    @if ($proceso->etapas->count() > 1)
                        <hr>
                        <p class="fw-semibold mb-2">Historial</p>
                        <ul class="list-unstyled mb-0">
                            @foreach ($proceso->etapas->sortByDesc('created_at')->skip(1) as $etapa)
                                <li class="mb-2 text-muted">
                                    <strong>{{ $etapa->created_at->format('d/m/Y H:i') }}</strong>
                                    — {{ $etapa->etapa }}
                                    ({{ $etapa->personal?->nombre }} {{ $etapa->personal?->apellidos }})
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Línea de tiempo</h3></div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        @foreach ($timeline as $evento)
                            <li class="mb-2">
                                <strong>{{ \Illuminate\Support\Carbon::parse($evento['fecha'])->format('d/m/Y H:i') }}</strong>
                                — {{ $evento['descripcion'] }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            @if ($proceso->finanza)
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Finanzas</h3></div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-4">Costo</dt>
                            <dd class="col-8">Bs. {{ number_format($proceso->finanza->costo, 2) }}</dd>
                            <dt class="col-4">Tipo de pago</dt>
                            <dd class="col-8">{{ $proceso->finanza->tipo_pago->label() }}</dd>
                            <dt class="col-4">Anticipo</dt>
                            <dd class="col-8">
                                Bs. {{ number_format($proceso->finanza->anticipo, 2) }}
                                @if ($proceso->finanza->anticipo > 0)
                                    @if ($proceso->finanza->anticipo_confirmado_en)
                                        <span class="badge text-bg-success">Confirmado</span>
                                    @else
                                        <form action="{{ route('admin.procesos.confirmar-anticipo', $proceso) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('¿Confirmar el anticipo y emitir el recibo?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success">Confirmar anticipo</button>
                                        </form>
                                    @endif
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Recibos emitidos</h3>
                        <a href="{{ route('admin.finanzas.recibos.create') }}" class="btn btn-sm btn-outline-primary">Registrar otro</a>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>N°</th><th>Fecha</th><th>Concepto</th><th class="text-end">Monto</th></tr></thead>
                            <tbody>
                                @forelse ($proceso->recibos->sortByDesc('fecha') as $recibo)
                                    <tr>
                                        <td><a href="{{ route('admin.finanzas.recibos.show', $recibo) }}">{{ $recibo->numero }}</a></td>
                                        <td>{{ $recibo->fecha->format('d/m/Y') }}</td>
                                        <td>{{ $recibo->concepto }}</td>
                                        <td class="text-end">Bs. {{ number_format((float) $recibo->monto, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">Sin recibos todavía.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Gastos del caso</h3>
                        <a href="{{ route('admin.finanzas.gastos.create') }}" class="btn btn-sm btn-outline-primary">Registrar gasto</a>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Fecha</th><th>Categoría</th><th class="text-end">Monto</th></tr></thead>
                            <tbody>
                                @forelse ($proceso->gastos->sortByDesc('fecha') as $gasto)
                                    <tr>
                                        <td>{{ $gasto->fecha->format('d/m/Y') }}</td>
                                        <td>{{ $gasto->categoria->label() }}</td>
                                        <td class="text-end">Bs. {{ number_format((float) $gasto->monto, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-3">Sin gastos registrados para este caso.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h3 class="card-title">Plan de pagos</h3></div>
                    <div class="card-body p-0">
                        @if ($proceso->finanza->cuotas->isEmpty())
                            <div class="p-3">
                                <form method="POST" action="{{ route('admin.procesos.generar-plan-pagos', $proceso) }}" class="row g-2">
                                    @csrf
                                    <div class="col-6">
                                        <label class="form-label">N° de cuotas</label>
                                        <input type="number" name="cuotas" min="1" class="form-control" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Fecha primera cuota</label>
                                        <input type="date" name="fecha_primera_cuota" class="form-control" required>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">Generar plan de pagos</button>
                                    </div>
                                </form>
                            </div>
                        @else
                            <table class="table table-sm mb-0">
                                <thead><tr><th>#</th><th>Fecha</th><th>Monto</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
                                <tbody>
                                    @foreach ($proceso->finanza->cuotas as $i => $cuota)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $cuota->fecha->format('d/m/Y') }}</td>
                                            <td>Bs. {{ number_format($cuota->monto, 2) }}</td>
                                            <td>
                                                @php $badge = match($cuota->estado->value) {
                                                    'pagado' => 'success', 'pendiente_confirmacion' => 'warning',
                                                    'vencido' => 'danger', default => 'secondary',
                                                }; @endphp
                                                <span class="badge text-bg-{{ $badge }}">{{ $cuota->estado->label() }}</span>
                                            </td>
                                            <td class="text-end">
                                                @if ($cuota->estado->value !== 'pagado')
                                                    <form action="{{ route('admin.plan-pagos.confirmar', $cuota) }}" method="POST" class="d-inline"
                                                        onsubmit="return confirm('¿Confirmar este pago?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success">Confirmar pago</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Expediente</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.procesos.documentos.store', $proceso) }}"
                        enctype="multipart/form-data" class="row g-2 align-items-end mb-4">
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label">Categoría</label>
                            <select name="categoria" class="form-select @error('categoria') is-invalid @enderror" required>
                                <option value="">Seleccione...</option>
                                @foreach (\App\Enums\CategoriaDocumento::cases() as $categoria)
                                    <option value="{{ $categoria->value }}">{{ $categoria->label() }}</option>
                                @endforeach
                            </select>
                            @error('categoria') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Nombre / descripción</label>
                            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" placeholder="Ej: CI Juan Pérez">
                            @error('nombre') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Archivo</label>
                            <input type="file" name="archivo" class="form-control @error('archivo') is-invalid @enderror" required>
                            <div class="form-text">PDF, JPG, PNG, Word o Excel — máx. 10 MB.</div>
                            @error('archivo') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-upload"></i> Subir
                            </button>
                        </div>
                    </form>

                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th>Nombre</th>
                                <th>Subido por</th>
                                <th>Fecha</th>
                                <th>Tamaño</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($proceso->documentos->sortByDesc('created_at') as $documento)
                                <tr>
                                    <td><span class="badge text-bg-secondary">{{ $documento->categoria->label() }}</span></td>
                                    <td>{{ $documento->nombre }}</td>
                                    <td>{{ $documento->subidoPor() ?? '—' }}</td>
                                    <td>{{ $documento->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $documento->tamanoLegible() }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.documentos.descargar', $documento) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-download"></i> Descargar
                                        </a>
                                        <form action="{{ route('admin.documentos.destroy', $documento) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('¿Eliminar este documento? No se puede deshacer.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-3">Sin documentos todavía.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
