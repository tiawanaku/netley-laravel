@extends('layouts.admin')

@section('page_title', 'Consultas')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Buscar nombre, CI o teléfono"
                    value="{{ request('buscar') }}" style="width: 260px;">
                <select name="estado" class="form-select form-select-sm" style="width: 180px;">
                    <option value="">Todos los estados</option>
                    @foreach ($estados as $estado)
                        <option value="{{ $estado->value }}" @selected(request('estado') === $estado->value)>{{ $estado->label() }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-outline-secondary">Filtrar</button>
            </form>
            <a href="{{ route('admin.consultas.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Nueva consulta
            </a>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Materia legal</th>
                        <th>Estado</th>
                        <th>Registrada</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($consultas as $consulta)
                        <tr>
                            <td>{{ $consulta->nombre }} {{ $consulta->apellido_paterno }}</td>
                            <td>{{ $consulta->telefono }}</td>
                            <td>{{ $consulta->materiaLegal?->nombre ?? '—' }}</td>
                            <td>
                                @php $badge = match($consulta->estado->value) {
                                    'nueva' => 'primary', 'agendada', 'reagendada' => 'info',
                                    'cliente_ejecutivo' => 'success', 'no_asistio', 'descartada' => 'secondary',
                                    default => 'secondary',
                                }; @endphp
                                <span class="badge text-bg-{{ $badge }}">{{ $consulta->estado->label() }}</span>
                            </td>
                            <td>{{ $consulta->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.consultas.show', $consulta) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                @unless ($consulta->estado->value === 'cliente_ejecutivo')
                                    <a href="{{ route('admin.consultas.edit', $consulta) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                @endunless
                                <form action="{{ route('admin.consultas.destroy', $consulta) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('¿Eliminar esta consulta?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Sin registros todavía.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($consultas->hasPages())
            <div class="card-footer">{{ $consultas->links() }}</div>
        @endif
    </div>
@stop
