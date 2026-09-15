@extends('layouts.admin')

@section('page_title', 'Alta directa de Cliente Ejecutivo')

@section('content')
    <div class="card" style="max-width: 900px;">
        <div class="card-header">
            <h3 class="card-title">Nuevo Cliente Ejecutivo (sin consulta previa)</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.clientes.store') }}">
                @csrf

                <h5>1. Datos del cliente</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}">
                        @error('nombre') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Apellidos</label>
                        <input type="text" name="apellidos" class="form-control @error('apellidos') is-invalid @enderror" value="{{ old('apellidos') }}">
                        @error('apellidos') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">CI</label>
                        <input type="text" name="ci" class="form-control @error('ci') is-invalid @enderror" value="{{ old('ci') }}">
                        @error('ci') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror" value="{{ old('telefono') }}" placeholder="7XXXXXXX">
                        @error('telefono') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">WhatsApp</label>
                        <input type="text" name="whatsapp" class="form-control @error('whatsapp') is-invalid @enderror" value="{{ old('whatsapp') }}" placeholder="7XXXXXXX">
                        @error('whatsapp') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <hr>
                <h5>2. Datos del proceso</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Materia legal</label>
                        <select name="materia_legal_id" id="materia_legal_id" class="form-select @error('materia_legal_id') is-invalid @enderror">
                            <option value="">— Seleccionar —</option>
                            @foreach ($materiasLegales as $materia)
                                <option value="{{ $materia->id }}" @selected(old('materia_legal_id') == $materia->id)>{{ $materia->nombre }}</option>
                            @endforeach
                        </select>
                        @error('materia_legal_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tipo de proceso / delito</label>
                        <input type="text" name="tipo_proceso" id="tipo_proceso" list="delitos-lista"
                            class="form-control @error('tipo_proceso') is-invalid @enderror" value="{{ old('tipo_proceso') }}"
                            placeholder="Seleccione materia legal primero" autocomplete="off">
                        <datalist id="delitos-lista"></datalist>
                        @error('tipo_proceso') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tiempo estimado (meses)</label>
                        <input type="number" name="tiempo_proceso_meses" min="1" class="form-control @error('tiempo_proceso_meses') is-invalid @enderror" value="{{ old('tiempo_proceso_meses', 6) }}">
                        @error('tiempo_proceso_meses') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Abogado asignado</label>
                        <select name="abogado_id" id="abogado_id" class="form-select @error('abogado_id') is-invalid @enderror">
                            <option value="">Seleccione materia legal primero</option>
                        </select>
                        @error('abogado_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        <div class="form-text">Filtrado por la especialidad del abogado según la materia legal elegida.</div>
                    </div>
                </div>

                <hr>
                <h5>3. Finanzas</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Costo total (Bs.)</label>
                        <input type="number" step="0.01" name="costo" class="form-control @error('costo') is-invalid @enderror" value="{{ old('costo') }}">
                        @error('costo') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tipo de pago</label>
                        <select name="tipo_pago" class="form-select @error('tipo_pago') is-invalid @enderror">
                            @foreach ($tiposPago as $tipo)
                                <option value="{{ $tipo->value }}" @selected(old('tipo_pago') === $tipo->value)>{{ $tipo->label() }}</option>
                            @endforeach
                        </select>
                        @error('tipo_pago') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Anticipo (Bs.)</label>
                        <input type="number" step="0.01" name="anticipo" class="form-control @error('anticipo') is-invalid @enderror" value="{{ old('anticipo', 0) }}">
                        @error('anticipo') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">N° de cuotas (opcional)</label>
                        <input type="number" min="1" name="cuotas" class="form-control @error('cuotas') is-invalid @enderror" value="{{ old('cuotas') }}">
                        @error('cuotas') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Fecha primera cuota</label>
                        <input type="date" name="fecha_primera_cuota" class="form-control @error('fecha_primera_cuota') is-invalid @enderror" value="{{ old('fecha_primera_cuota') }}">
                        @error('fecha_primera_cuota') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        <div class="form-text">Si se indican cuotas, el plan de pagos se genera automáticamente al guardar.</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Crear Cliente Ejecutivo</button>
                <a href="{{ route('admin.clientes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var materiaSelect = document.getElementById('materia_legal_id');
            var delitosLista = document.getElementById('delitos-lista');
            var abogadoSelect = document.getElementById('abogado_id');

            function actualizar() {
                var materiaId = materiaSelect.value;
                delitosLista.innerHTML = '';
                abogadoSelect.innerHTML = '<option value="">Cargando…</option>';

                if (!materiaId) {
                    abogadoSelect.innerHTML = '<option value="">Seleccione materia legal primero</option>';
                    return;
                }

                fetch('{{ route('admin.clientes.delitos-por-materia') }}?materia_legal_id=' + materiaId)
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        Object.values(data).forEach(function (nombre) {
                            var opt = document.createElement('option');
                            opt.value = nombre;
                            delitosLista.appendChild(opt);
                        });
                    });

                fetch('{{ route('admin.clientes.abogados-por-materia') }}?materia_legal_id=' + materiaId)
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        abogadoSelect.innerHTML = '';
                        if (data.length === 0) {
                            abogadoSelect.innerHTML = '<option value="">Sin abogados con esa especialidad</option>';
                            return;
                        }
                        abogadoSelect.innerHTML = '<option value="">— Seleccionar —</option>';
                        data.forEach(function (abogado) {
                            var opt = document.createElement('option');
                            opt.value = abogado.id;
                            opt.textContent = abogado.nombre + ' ' + abogado.apellidos;
                            abogadoSelect.appendChild(opt);
                        });
                    });
            }

            materiaSelect.addEventListener('change', actualizar);
        })();
    </script>
@stop
