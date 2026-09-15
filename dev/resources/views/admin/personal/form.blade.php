@extends('layouts.admin')

@section('page_title', ($registro->exists ? 'Editar' : 'Nuevo').' Personal')

@section('content')
    <div class="card" style="max-width: 960px;">
        <div class="card-header">
            <h3 class="card-title">{{ $registro->exists ? 'Editar' : 'Nuevo' }} Personal</h3>
        </div>
        <div class="card-body">
            <form method="POST"
                action="{{ $registro->exists ? route('admin.personal.update', $registro) : route('admin.personal.store') }}">
                @csrf
                @if ($registro->exists)
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nombres</label>
                        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                            value="{{ old('nombre', $registro->nombre) }}">
                        @error('nombre') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Apellido Paterno</label>
                        <input type="text" name="apellido_paterno" class="form-control @error('apellido_paterno') is-invalid @enderror"
                            value="{{ old('apellido_paterno', $registro->apellido_paterno) }}">
                        @error('apellido_paterno') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Apellido Materno</label>
                        <input type="text" name="apellido_materno" class="form-control @error('apellido_materno') is-invalid @enderror"
                            value="{{ old('apellido_materno', $registro->apellido_materno) }}">
                        @error('apellido_materno') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">CI</label>
                        <input type="text" name="ci" class="form-control @error('ci') is-invalid @enderror"
                            value="{{ old('ci', $registro->ci) }}">
                        @error('ci') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Expedido</label>
                        <select name="expedido" id="expedido" class="form-select @error('expedido') is-invalid @enderror">
                            <option value="">— Seleccionar —</option>
                            @foreach ($expedidos as $exp)
                                <option value="{{ $exp->value }}" @selected(old('expedido', $registro->expedido?->value) === $exp->value)>
                                    {{ $exp->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('expedido') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3 mb-3" id="campo-expedido-otro">
                        <label class="form-label">Especifique expedido</label>
                        <input type="text" name="expedido_otro" class="form-control @error('expedido_otro') is-invalid @enderror"
                            value="{{ old('expedido_otro', $registro->expedido_otro) }}">
                        @error('expedido_otro') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Género</label>
                        <select name="genero" class="form-select @error('genero') is-invalid @enderror">
                            <option value="">— Seleccionar —</option>
                            @foreach ($generos as $genero)
                                <option value="{{ $genero->value }}" @selected(old('genero', $registro->genero?->value) === $genero->value)>
                                    {{ $genero->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('genero') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Fecha de nacimiento</label>
                        <input type="date" name="fecha_nacimiento" class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                            value="{{ old('fecha_nacimiento', $registro->fecha_nacimiento?->format('Y-m-d')) }}">
                        @error('fecha_nacimiento') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nacionalidad</label>
                        <input type="text" name="nacionalidad" class="form-control @error('nacionalidad') is-invalid @enderror"
                            value="{{ old('nacionalidad', $registro->nacionalidad ?? 'Boliviana') }}">
                        @error('nacionalidad') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Estado civil</label>
                        <select name="estado_civil" class="form-select @error('estado_civil') is-invalid @enderror">
                            <option value="">— Seleccionar —</option>
                            @foreach ($estadosCiviles as $ec)
                                <option value="{{ $ec->value }}" @selected(old('estado_civil', $registro->estado_civil?->value) === $ec->value)>
                                    {{ $ec->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('estado_civil') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror"
                            value="{{ old('telefono', $registro->telefono) }}" placeholder="7XXXXXXX">
                        @error('telefono') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">WhatsApp</label>
                        <input type="text" name="whatsapp" class="form-control @error('whatsapp') is-invalid @enderror"
                            value="{{ old('whatsapp', $registro->whatsapp) }}" placeholder="7XXXXXXX">
                        @error('whatsapp') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $registro->email) }}">
                        @error('email') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-8 mb-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion" class="form-control @error('direccion') is-invalid @enderror"
                            value="{{ old('direccion', $registro->direccion) }}">
                        @error('direccion') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Ciudad</label>
                        <select name="departamento_id" class="form-select @error('departamento_id') is-invalid @enderror">
                            <option value="">— Seleccionar —</option>
                            @foreach ($departamentos as $departamento)
                                <option value="{{ $departamento->id }}" @selected(old('departamento_id', $registro->departamento_id) == $departamento->id)>
                                    {{ $departamento->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('departamento_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">N° de contrato</label>
                        <input type="text" name="numero_contrato" class="form-control @error('numero_contrato') is-invalid @enderror"
                            value="{{ old('numero_contrato', $registro->numero_contrato) }}">
                        @error('numero_contrato') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Fecha de inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control @error('fecha_inicio') is-invalid @enderror"
                            value="{{ old('fecha_inicio', $registro->fecha_inicio?->format('Y-m-d')) }}">
                        @error('fecha_inicio') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Fecha de fin</label>
                        <input type="date" name="fecha_fin" class="form-control @error('fecha_fin') is-invalid @enderror"
                            value="{{ old('fecha_fin', $registro->fecha_fin?->format('Y-m-d')) }}">
                        @error('fecha_fin') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select @error('estado') is-invalid @enderror">
                            @foreach ($estadosPersonal as $ep)
                                <option value="{{ $ep->value }}" @selected(old('estado', $registro->estado?->value ?? 'habilitado') === $ep->value)>
                                    {{ $ep->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('estado') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Rol (acceso al panel)</label>
                        <select name="rol" class="form-select @error('rol') is-invalid @enderror">
                            <option value="">— Seleccionar —</option>
                            @foreach ($roles as $rol)
                                <option value="{{ $rol->value }}" @selected(old('rol', $registro->rol?->value) === $rol->value)>
                                    {{ $rol->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('rol') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Cargo (puesto)</label>
                        <select name="cargo" class="form-select @error('cargo') is-invalid @enderror">
                            <option value="">— Seleccionar —</option>
                            @foreach ($cargos as $c)
                                <option value="{{ $c->value }}" @selected(old('cargo', $registro->cargo) === $c->value)>
                                    {{ $c->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('cargo') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <hr>
                <label class="form-label d-block">Profesiones <small class="text-muted">(seleccione al menos una — habilita las especialidades correspondientes)</small></label>
                <div class="mb-3 d-flex flex-wrap gap-3" id="campo-profesiones">
                    @php $profesionesActuales = old('profesiones', $registro->profesiones ?? []); @endphp
                    @foreach ($profesionesDisponibles as $p)
                        <div class="form-check">
                            <input type="checkbox" name="profesiones[]" value="{{ $p->value }}" id="prof-{{ $p->value }}"
                                class="form-check-input profesion-check" data-grupo="{{ $p->value }}"
                                @checked(in_array($p->value, $profesionesActuales))>
                            <label class="form-check-label" for="prof-{{ $p->value }}">{{ $p->label() }}</label>
                        </div>
                    @endforeach
                </div>
                @error('profesiones') <span class="text-danger d-block mb-3">{{ $message }}</span> @enderror

                <div class="mb-3" id="campo-profesion-otra" style="display:none;">
                    <label class="form-label">Especifique la otra profesión</label>
                    <input type="text" name="profesiones_otro" class="form-control @error('profesiones_otro') is-invalid @enderror"
                        value="{{ old('profesiones_otro', $registro->profesiones_otro) }}"
                        placeholder="Separar por comas si son varias">
                    @error('profesiones_otro') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>

                @php $especialidadesActuales = old('especialidades', $registro->especialidades ?? []); @endphp

                <div class="mb-3 especialidad-grupo" data-grupo="{{ \App\Enums\ProfesionPersonal::Abogado->value }}" style="display:none;">
                    <label class="form-label">Especialidades legales</label>
                    <div class="d-flex flex-wrap gap-2 p-3 bg-light rounded">
                        @foreach ($especialidadesLegales as $e)
                            <div class="form-check">
                                <input type="checkbox" name="especialidades[]" value="{{ $e->value }}" id="esp-{{ Str::slug($e->value) }}"
                                    class="form-check-input" @checked(in_array($e->value, $especialidadesActuales))>
                                <label class="form-check-label" for="esp-{{ Str::slug($e->value) }}">{{ $e->label() }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3 especialidad-grupo" data-grupo="{{ \App\Enums\ProfesionPersonal::Psicologia->value }}" style="display:none;">
                    <label class="form-label">Especialidades de psicología</label>
                    <div class="d-flex flex-wrap gap-2 p-3 bg-light rounded">
                        @foreach ($especialidadesPsicologia as $e)
                            <div class="form-check">
                                <input type="checkbox" name="especialidades[]" value="{{ $e->value }}" id="esp-{{ Str::slug($e->value) }}"
                                    class="form-check-input" @checked(in_array($e->value, $especialidadesActuales))>
                                <label class="form-check-label" for="esp-{{ Str::slug($e->value) }}">{{ $e->label() }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3 especialidad-grupo" data-grupo="{{ \App\Enums\ProfesionPersonal::Medico->value }}" style="display:none;">
                    <label class="form-label">Especialidades médicas</label>
                    <div class="d-flex flex-wrap gap-2 p-3 bg-light rounded">
                        @foreach ($especialidadesMedicas as $e)
                            <div class="form-check">
                                <input type="checkbox" name="especialidades[]" value="{{ $e->value }}" id="esp-{{ Str::slug($e->value) }}"
                                    class="form-check-input" @checked(in_array($e->value, $especialidadesActuales))>
                                <label class="form-check-label" for="esp-{{ Str::slug($e->value) }}">{{ $e->label() }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3 especialidad-grupo" data-grupo="{{ \App\Enums\ProfesionPersonal::TrabajoSocial->value }}" style="display:none;">
                    <label class="form-label">Especialidades de trabajo social</label>
                    <div class="d-flex flex-wrap gap-2 p-3 bg-light rounded">
                        @foreach ($especialidadesTrabajoSocial as $e)
                            <div class="form-check">
                                <input type="checkbox" name="especialidades[]" value="{{ $e->value }}" id="esp-{{ Str::slug($e->value) }}"
                                    class="form-check-input" @checked(in_array($e->value, $especialidadesActuales))>
                                <label class="form-check-label" for="esp-{{ Str::slug($e->value) }}">{{ $e->label() }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nota Netley</label>
                    <textarea name="nota" class="form-control @error('nota') is-invalid @enderror" rows="2">{{ old('nota', $registro->nota) }}</textarea>
                    @error('nota') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                </div>

                @unless ($registro->exists)
                    <p class="text-muted">El usuario y la contraseña temporal del portal se generan automáticamente al guardar.</p>
                @endunless

                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="{{ route('admin.personal.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var expedido = document.getElementById('expedido');
            var campoExpedidoOtro = document.getElementById('campo-expedido-otro');

            function toggleExpedidoOtro() {
                campoExpedidoOtro.style.display = expedido.value === 'OTRO' ? '' : 'none';
            }
            expedido.addEventListener('change', toggleExpedidoOtro);
            toggleExpedidoOtro();

            var checks = document.querySelectorAll('.profesion-check');
            var grupos = document.querySelectorAll('.especialidad-grupo');
            var campoOtra = document.getElementById('campo-profesion-otra');
            var checkOtros = document.getElementById('prof-Otros');

            function toggleEspecialidades() {
                var marcadas = Array.from(checks).filter(function (c) { return c.checked; }).map(function (c) { return c.dataset.grupo; });
                grupos.forEach(function (grupo) {
                    var visible = marcadas.indexOf(grupo.dataset.grupo) !== -1;
                    grupo.style.display = visible ? '' : 'none';
                    if (!visible) {
                        grupo.querySelectorAll('input[type=checkbox]').forEach(function (cb) { cb.checked = false; });
                    }
                });
                if (campoOtra && checkOtros) {
                    campoOtra.style.display = checkOtros.checked ? '' : 'none';
                    if (!checkOtros.checked) {
                        campoOtra.querySelector('input').value = '';
                    }
                }
            }

            checks.forEach(function (c) { c.addEventListener('change', toggleEspecialidades); });
            toggleEspecialidades();
        })();
    </script>
@stop
