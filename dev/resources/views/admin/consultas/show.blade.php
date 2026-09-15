@extends('layouts.admin')

@section('page_title', 'Consulta: '.$consulta->nombre.' '.$consulta->apellido_paterno)

@section('content')
    @unless ($consulta->cliente)
        <div class="alert alert-danger d-flex align-items-center gap-2">
            <i class="bi bi-person-x"></i> No es Cliente Ejecutivo
        </div>
    @endunless

    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Datos de la consulta</h3>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">N° Consulta</dt>
                        <dd class="col-7">{{ $consulta->id }}</dd>
                        <dt class="col-5">Fecha de consulta</dt>
                        <dd class="col-7">{{ $consulta->created_at->format('Y-m-d H:i') }}</dd>
                        <dt class="col-5">Nombre</dt>
                        <dd class="col-7">{{ $consulta->nombre }} {{ $consulta->apellido_paterno }} {{ $consulta->apellido_materno }}</dd>
                        <dt class="col-5">CI</dt>
                        <dd class="col-7">{{ $consulta->ci ?? '—' }}</dd>
                        <dt class="col-5">Teléfono</dt>
                        <dd class="col-7">{{ $consulta->telefono }}</dd>
                        <dt class="col-5">WhatsApp</dt>
                        <dd class="col-7">{{ $consulta->whatsapp ?? '—' }}</dd>
                        <dt class="col-5">Email</dt>
                        <dd class="col-7">{{ $consulta->email ?? '—' }}</dd>
                        <dt class="col-5">Ciudad</dt>
                        <dd class="col-7">{{ $consulta->departamento?->nombre ?? '—' }}</dd>
                        <dt class="col-5">Provincia</dt>
                        <dd class="col-7">{{ $consulta->provincia ?? '—' }}</dd>
                        <dt class="col-5">País</dt>
                        <dd class="col-7">{{ $consulta->pais ?? '—' }}</dd>
                        <dt class="col-5">Materia legal</dt>
                        <dd class="col-7">{{ $consulta->materiaLegal?->nombre ?? '—' }}</dd>
                        <dt class="col-5">Origen</dt>
                        <dd class="col-7">
                            {{ $consulta->origen?->nombre ?? '—' }}
                            @if ($consulta->colegio_otros)
                                : {{ $consulta->colegio_otros }}
                            @elseif ($consulta->origen_otro)
                                : {{ $consulta->origen_otro }}
                            @endif
                        </dd>
                        <dt class="col-5">Estado</dt>
                        <dd class="col-7"><span class="badge text-bg-info">{{ $consulta->estado->label() }}</span></dd>
                    </dl>
                    @if ($consulta->descripcion)
                        <hr>
                        <strong>Consulta:</strong>
                        <p class="mb-0">{{ $consulta->descripcion }}</p>
                    @endif
                    @if ($consulta->nota_interna)
                        <hr>
                        <strong>Nota interna:</strong>
                        <p class="mb-0">{{ $consulta->nota_interna }}</p>
                    @endif
                </div>
            </div>

            @if ($consulta->cliente)
                <div class="card card-outline card-success">
                    <div class="card-header"><h3 class="card-title">Cliente Ejecutivo</h3></div>
                    <div class="card-body">
                        <p class="mb-0">Ya convertido — usuario <code>{{ $consulta->cliente->usuario }}</code></p>
                    </div>
                </div>
            @else
                <div class="card card-outline card-primary">
                    <div class="card-header"><h3 class="card-title">Convertir a Cliente Ejecutivo</h3></div>
                    <div class="card-body">
                        <p class="text-muted">Copia los datos de contacto y genera usuario/contraseña del portal cliente.</p>
                        <form method="POST" action="{{ route('admin.consultas.convertir', $consulta) }}"
                            onsubmit="return confirm('¿Convertir esta consulta a Cliente Ejecutivo? No se puede deshacer.');">
                            @csrf
                            <button type="submit" class="btn btn-primary">Nuevo Cliente Ejecutivo</button>
                        </form>
                        @error('consulta')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-7">
            @if ($puedeAgendarCita || $puedeAgendarLlamada)
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Agendar</h3></div>
                    <div class="card-body">
                        <form method="POST" id="form-agendar" class="row g-2">
                            @csrf
                            <div class="col-md-6">
                                <label class="form-label">Responsable</label>
                                <select name="responsable_id" class="form-select @error('responsable_id') is-invalid @enderror">
                                    <option value="">— Seleccionar —</option>
                                    @foreach ($personal as $p)
                                        <option value="{{ $p->id }}">{{ $p->nombre }} {{ $p->apellidos }}</option>
                                    @endforeach
                                </select>
                                @error('responsable_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Modalidad</label>
                                <select name="modalidad" class="form-select @error('modalidad') is-invalid @enderror">
                                    <option value="presencial">Presencial</option>
                                    <option value="virtual">Virtual</option>
                                </select>
                                @error('modalidad') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha</label>
                                <input type="date" id="agendar_fecha" name="fecha" class="form-control @error('fecha') is-invalid @enderror" min="{{ now()->toDateString() }}">
                                @error('fecha') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hora (bloques de 15 min)</label>
                                <select id="agendar_hora" name="hora" class="form-select @error('hora') is-invalid @enderror" disabled>
                                    <option value="">Selecciona responsable y fecha</option>
                                </select>
                                @error('hora') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Ubicación</label>
                                <input type="text" name="ubicacion" class="form-control" placeholder="Oficina Central, Virtual, etc.">
                            </div>
                            <div class="col-md-4 d-flex align-items-end gap-2">
                                @if ($puedeAgendarCita)
                                    <button type="submit" formaction="{{ route('admin.consultas.agendar-cita', $consulta) }}" class="btn btn-success w-100">
                                        <i class="bi bi-calendar-event"></i> Agendar
                                    </button>
                                @endif
                                @if ($puedeAgendarLlamada)
                                    <button type="submit" formaction="{{ route('admin.consultas.agendar-llamada', $consulta) }}" class="btn btn-warning w-100">
                                        <i class="bi bi-telephone"></i> Agendar llamada
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header"><h3 class="card-title">Citas y llamadas agendadas</h3></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Tipo</th><th>Fecha</th><th>Responsable</th><th>Estado</th></tr></thead>
                        <tbody>
                            @forelse ($consulta->agendas as $agenda)
                                <tr>
                                    <td>{{ $agenda->tipo->label() }}</td>
                                    <td>{{ $agenda->fecha_inicio->format('d/m/Y H:i') }}</td>
                                    <td>{{ $agenda->responsable?->nombre }} {{ $agenda->responsable?->apellidos }}</td>
                                    <td>{{ $agenda->estado->label() }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Sin registros todavía.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($puedeResponder)
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Añadir Nueva Respuesta</h3></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.consultas.dar-respuesta', $consulta) }}" id="form-respuesta">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Respuesta</label>
                                <textarea name="respuesta" class="form-control @error('respuesta') is-invalid @enderror" rows="3"></textarea>
                                @error('respuesta') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-3 p-2 bg-light rounded d-flex align-items-center gap-3">
                                <span class="fw-semibold">Publicar</span>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="publicar" id="publicar_si" value="1">
                                    <label class="form-check-label" for="publicar_si">Sí</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="publicar" id="publicar_no" value="0" checked>
                                    <label class="form-check-label" for="publicar_no">No</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Categoría</label>
                                <select name="categoria" id="categoria" class="form-select @error('categoria') is-invalid @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach ($categoriasRespuesta as $cat)
                                        <option value="{{ $cat->value }}">{{ $cat->label() }}</option>
                                    @endforeach
                                </select>
                                @error('categoria') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-3" id="campo-materia-legal" style="display:none;">
                                <label class="form-label">Materia legal</label>
                                <select name="materia_legal_id" id="materia_legal_id" class="form-select @error('materia_legal_id') is-invalid @enderror">
                                    <option value="">Seleccione materia legal...</option>
                                    @foreach ($materiasLegales as $materia)
                                        <option value="{{ $materia->id }}">{{ $materia->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('materia_legal_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror

                                <div class="mt-2" id="campo-delito" style="display:none;">
                                    <label class="form-label">Delito relacionado</label>
                                    <select name="delito_id" id="delito_id" class="form-select">
                                        <option value="">Seleccione materia legal primero...</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-dark">
                                <i class="bi bi-send"></i> Responder
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header"><h3 class="card-title">Respuestas registradas</h3></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Fecha</th><th>Respondida por</th><th>Categoría</th><th>Contenido</th><th>Publicar</th></tr></thead>
                        <tbody>
                            @forelse ($respuestas as $r)
                                <tr>
                                    <td>{{ $r['fecha']->format('d/m/Y H:i') }}</td>
                                    <td>{{ $r['respondida_por']->nombre ?? $r['respondida_por']->name ?? '—' }}</td>
                                    <td>
                                        {{ $r['categoria']?->label() ?? '—' }}
                                        @if ($r['materia_legal'])
                                            <br><small class="text-muted">{{ $r['materia_legal']->nombre }}@if($r['delito']) — {{ $r['delito']->delito }}@endif</small>
                                        @endif
                                    </td>
                                    <td>{{ $r['contenido'] }}</td>
                                    <td>{{ $r['publicar'] ? 'Sí' : 'No' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">Sin respuestas todavía.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var form = document.getElementById('form-agendar');
            if (form) {
                form.addEventListener('submit', function (e) {
                    var btn = e.submitter;
                    if (btn && btn.formAction) form.action = btn.formAction;
                });
            }

            var responsableSelect = document.querySelector('select[name="responsable_id"]');
            var fechaInput = document.getElementById('agendar_fecha');
            var horaSelect = document.getElementById('agendar_hora');

            function cargarHorarios() {
                if (!responsableSelect || !fechaInput || !horaSelect) return;

                var responsableId = responsableSelect.value;
                var fecha = fechaInput.value;

                if (!responsableId || !fecha) {
                    horaSelect.innerHTML = '<option value="">Selecciona responsable y fecha</option>';
                    horaSelect.disabled = true;
                    return;
                }

                horaSelect.innerHTML = '<option value="">Cargando horarios...</option>';
                horaSelect.disabled = true;

                fetch('{{ route('admin.consultas.horarios-disponibles') }}?responsable_id=' + encodeURIComponent(responsableId) + '&fecha=' + encodeURIComponent(fecha))
                    .then(function (r) { return r.json(); })
                    .then(function (horarios) {
                        if (!horarios.length) {
                            horaSelect.innerHTML = '<option value="">Sin horarios disponibles ese día</option>';
                            horaSelect.disabled = true;
                            return;
                        }
                        var html = '<option value="">— Seleccionar —</option>';
                        horarios.forEach(function (h) {
                            html += '<option value="' + h + '">' + h + '</option>';
                        });
                        horaSelect.innerHTML = html;
                        horaSelect.disabled = false;
                    })
                    .catch(function () {
                        horaSelect.innerHTML = '<option value="">Error al cargar horarios</option>';
                        horaSelect.disabled = true;
                    });
            }

            if (responsableSelect) responsableSelect.addEventListener('change', cargarHorarios);
            if (fechaInput) fechaInput.addEventListener('change', cargarHorarios);

            var categoria = document.getElementById('categoria');
            var campoMateria = document.getElementById('campo-materia-legal');
            var materiaSelect = document.getElementById('materia_legal_id');
            var campoDelito = document.getElementById('campo-delito');
            var delitoSelect = document.getElementById('delito_id');

            if (categoria) {
                categoria.addEventListener('change', function () {
                    campoMateria.style.display = this.value === 'Legal' ? '' : 'none';
                });
            }

            if (materiaSelect) {
                materiaSelect.addEventListener('change', function () {
                    var materiaId = this.value;
                    if (!materiaId) {
                        campoDelito.style.display = 'none';
                        delitoSelect.innerHTML = '<option value="">Seleccione materia legal primero...</option>';
                        return;
                    }
                    fetch('{{ route('admin.clientes.delitos-por-materia') }}?materia_legal_id=' + materiaId)
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            var entries = Object.entries(data);
                            if (entries.length === 0) {
                                delitoSelect.innerHTML = '<option value="">No hay delitos para esta materia</option>';
                            } else {
                                var html = '<option value="">Seleccione delito...</option>';
                                entries.forEach(function (entry) {
                                    html += '<option value="' + entry[0] + '">' + entry[1] + '</option>';
                                });
                                delitoSelect.innerHTML = html;
                            }
                            campoDelito.style.display = '';
                        });
                });
            }
        })();
    </script>
@stop
