@extends('layouts.admin')

@section('page_title', 'Personal')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Buscar por nombre, CI, cargo o teléfono"
                    value="{{ request('buscar') }}" style="width: 320px;">
                <button type="submit" class="btn btn-sm btn-outline-secondary">Buscar</button>
            </form>
            <a href="{{ route('admin.personal.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Nuevo
            </a>
        </div>
        <div class="card-body p-0" style="overflow-x: auto;">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Nombres</th>
                        <th>Ap. Paterno</th>
                        <th>Ap. Materno</th>
                        <th>Ciudad</th>
                        <th>Cargo</th>
                        <th>Profesiones</th>
                        <th>Especialidad</th>
                        <th>Contrato</th>
                        <th>Fecha inicio</th>
                        <th>Fecha fin</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($personal as $registro)
                        <tr>
                            <td>{{ $registro->nombre }}</td>
                            <td>{{ $registro->apellido_paterno }}</td>
                            <td>{{ $registro->apellido_materno }}</td>
                            <td>{{ $registro->departamento?->nombre ?? '—' }}</td>
                            <td>{{ $registro->cargo ?? '—' }}</td>
                            <td>{{ collect($registro->profesiones)->implode(', ') ?: '—' }}</td>
                            <td>{{ collect($registro->especialidades)->implode(', ') ?: 'NO APLICA' }}</td>
                            <td>{{ $registro->numero_contrato ?? '—' }}</td>
                            <td>{{ $registro->fecha_inicio?->format('d/m/Y') }}</td>
                            <td>{{ $registro->fecha_fin?->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                <span class="badge text-bg-{{ $registro->estado->value === 'habilitado' ? 'success' : 'secondary' }}">
                                    {{ $registro->estado->label() }}
                                </span>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.personal.edit', $registro) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                <form action="{{ route('admin.personal.reset-acceso', $registro) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('¿Resetear el acceso al portal de {{ $registro->nombre }}?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-warning">Resetear acceso</button>
                                </form>
                                <form action="{{ route('admin.personal.destroy', $registro) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('¿Eliminar a {{ $registro->nombre }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">Sin registros todavía.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($personal->hasPages())
            <div class="card-footer">
                {{ $personal->links() }}
            </div>
        @endif
    </div>
@stop
