@extends('layouts.admin')

@section('page_title', $config['titulo'])

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">{{ $config['titulo'] }}</h3>
            <a href="{{ route('admin.catalogos.create', $catalogo) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Nuevo
            </a>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th>Nombre</th>
                        <th>Slug</th>
                        <th>Activo</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($registros as $registro)
                        <tr>
                            <td>{{ $registro->orden }}</td>
                            <td>{{ $registro->nombre }}</td>
                            <td><code>{{ $registro->slug }}</code></td>
                            <td>
                                @if ($registro->activo)
                                    <span class="badge text-bg-success">Activo</span>
                                @else
                                    <span class="badge text-bg-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.catalogos.edit', [$catalogo, $registro->id]) }}"
                                    class="btn btn-sm btn-outline-primary">Editar</a>
                                <form action="{{ route('admin.catalogos.destroy', [$catalogo, $registro->id]) }}"
                                    method="POST" class="d-inline"
                                    onsubmit="return confirm('¿Eliminar {{ $registro->nombre }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Sin registros todavía.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
