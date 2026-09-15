@extends('layouts.admin')

@section('page_title', ($registro->exists ? 'Editar' : 'Nueva').' Consulta')

@section('content')
    <div class="card" style="max-width: 900px;">
        <div class="card-header">
            <h3 class="card-title">{{ $registro->exists ? 'Editar' : 'Nueva' }} Consulta</h3>
        </div>
        <div class="card-body">
            <form method="POST"
                action="{{ $registro->exists ? route('admin.consultas.update', $registro) : route('admin.consultas.store') }}">
                @csrf
                @if ($registro->exists)
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                            value="{{ old('nombre', $registro->nombre) }}">
                        @error('nombre') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Apellido paterno</label>
                        <input type="text" name="apellido_paterno" class="form-control @error('apellido_paterno') is-invalid @enderror"
                            value="{{ old('apellido_paterno', $registro->apellido_paterno) }}">
                        @error('apellido_paterno') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Apellido materno</label>
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
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror"
                            value="{{ old('telefono', $registro->telefono) }}" placeholder="7XXXXXXX">
                        @error('telefono') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">WhatsApp</label>
                        <input type="text" name="whatsapp" class="form-control @error('whatsapp') is-invalid @enderror"
                            value="{{ old('whatsapp', $registro->whatsapp) }}" placeholder="Solo números, '+' opcional al inicio">
                        @error('whatsapp') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $registro->email) }}">
                        @error('email') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
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
                        <label class="form-label">Provincia</label>
                        <input type="text" name="provincia" class="form-control @error('provincia') is-invalid @enderror"
                            value="{{ old('provincia', $registro->provincia) }}">
                        @error('provincia') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">País</label>
                        <input type="text" name="pais" class="form-control @error('pais') is-invalid @enderror"
                            value="{{ old('pais', $registro->pais ?? 'Bolivia') }}">
                        @error('pais') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Materia legal <small class="text-muted">(opcional en esta etapa)</small></label>
                        <select name="materia_legal_id" class="form-select @error('materia_legal_id') is-invalid @enderror">
                            <option value="">— Seleccionar —</option>
                            @foreach ($materiasLegales as $materia)
                                <option value="{{ $materia->id }}" @selected(old('materia_legal_id', $registro->materia_legal_id) == $materia->id)>
                                    {{ $materia->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('materia_legal_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Origen de la consulta</label>
                        <select name="origen_id" id="origen_id" class="form-select @error('origen_id') is-invalid @enderror">
                            <option value="">Seleccione una opción</option>
                            @foreach ($origenes as $origen)
                                <option value="{{ $origen->id }}" data-slug="{{ $origen->slug }}"
                                    @selected(old('origen_id', $registro->origen_id) == $origen->id)>
                                    {{ $origen->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('origen_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        <input type="text" name="colegio_otros" id="colegio_otros" placeholder="Nombre del colegio"
                            class="form-control mt-2 @error('colegio_otros') is-invalid @enderror"
                            value="{{ old('colegio_otros', $registro->colegio_otros) }}" style="display:none;">
                        <input type="text" name="origen_otro" id="origen_otro" placeholder="Especifique el origen"
                            class="form-control mt-2 @error('origen_otro') is-invalid @enderror"
                            value="{{ old('origen_otro', $registro->origen_otro) }}" style="display:none;">
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Consulta</label>
                        <textarea name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" rows="3">{{ old('descripcion', $registro->descripcion) }}</textarea>
                        @error('descripcion') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Nota interna</label>
                        <textarea name="nota_interna" class="form-control @error('nota_interna') is-invalid @enderror" rows="2">{{ old('nota_interna', $registro->nota_interna) }}</textarea>
                        @error('nota_interna') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Registrar consulta</button>
                <a href="{{ route('admin.consultas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var origenSelect = document.getElementById('origen_id');
            var colegioInput = document.getElementById('colegio_otros');
            var otroInput = document.getElementById('origen_otro');

            function actualizar() {
                var opcion = origenSelect.options[origenSelect.selectedIndex];
                var slug = opcion ? opcion.dataset.slug : '';
                colegioInput.style.display = slug === 'taller-colegio' ? '' : 'none';
                otroInput.style.display = slug === 'otro' ? '' : 'none';
            }

            origenSelect.addEventListener('change', actualizar);
            actualizar();
        })();
    </script>
@stop
