@extends('layouts.admin')

@section('page_title', 'Cliente Ejecutivo')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Buscar por nombre, apellidos o CI"
                    value="{{ request('buscar') }}" style="width: 280px;">
                <button type="submit" class="btn btn-sm btn-outline-secondary">Buscar</button>
            </form>
            <a href="{{ route('admin.clientes.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Alta directa
            </a>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>CI</th>
                        <th>Teléfono</th>
                        <th>Usuario portal</th>
                        <th>Casos</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clientes as $cliente)
                        <tr>
                            <td>{{ $cliente->nombre }} {{ $cliente->apellidos }}</td>
                            <td>{{ $cliente->ci ?? '—' }}</td>
                            <td>{{ $cliente->telefono }}</td>
                            <td><code>{{ $cliente->usuario }}</code></td>
                            <td>{{ $cliente->procesos_count }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.clientes.show', $cliente) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                <a href="{{ route('admin.clientes.edit', $cliente) }}" class="btn btn-sm btn-outline-primary">Editar</a>
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
        @if ($clientes->hasPages())
            <div class="card-footer">{{ $clientes->links() }}</div>
        @endif
    </div>
@stop
