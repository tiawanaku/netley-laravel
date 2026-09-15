@extends('layouts.admin')

@section('page_title', ($registro->exists ? 'Editar ' : 'Nuevo(a) ').$config['singular'])

@section('content')
    <div class="card" style="max-width: 640px;">
        <div class="card-header">
            <h3 class="card-title">{{ $registro->exists ? 'Editar' : 'Nuevo(a)' }} {{ $config['singular'] }}</h3>
        </div>
        <div class="card-body">
            <form method="POST"
                action="{{ $registro->exists
                    ? route('admin.catalogos.update', [$catalogo, $registro->id])
                    : route('admin.catalogos.store', $catalogo) }}">
                @csrf
                @if ($registro->exists)
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" name="nombre" id="nombre"
                        class="form-control @error('nombre') is-invalid @enderror"
                        value="{{ old('nombre', $registro->nombre) }}" autofocus>
                    @error('nombre')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label">Slug</label>
                    <input type="text" name="slug" id="slug"
                        class="form-control @error('slug') is-invalid @enderror"
                        value="{{ old('slug', $registro->slug) }}">
                    @error('slug')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="orden" class="form-label">Orden</label>
                    <input type="number" name="orden" id="orden" min="0"
                        class="form-control @error('orden') is-invalid @enderror"
                        value="{{ old('orden', $registro->orden ?? 0) }}">
                    @error('orden')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="activo" id="activo" class="form-check-input" value="1"
                        {{ old('activo', $registro->activo ?? true) ? 'checked' : '' }}>
                    <label for="activo" class="form-check-label">Activo</label>
                </div>

                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="{{ route('admin.catalogos.index', $catalogo) }}" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>

    <script>
        // Autogenera el slug desde el nombre, sin pisar una edicion manual ya hecha.
        (function () {
            var nombre = document.getElementById('nombre');
            var slug = document.getElementById('slug');
            var slugTocado = {{ $registro->exists ? 'true' : 'false' }};
            var combining = new RegExp('[̀-ͯ]', 'g');

            slug.addEventListener('input', function () { slugTocado = true; });

            nombre.addEventListener('input', function () {
                if (slugTocado) return;
                slug.value = nombre.value
                    .toLowerCase()
                    .normalize('NFD').replace(combining, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            });
        })();
    </script>
@stop
