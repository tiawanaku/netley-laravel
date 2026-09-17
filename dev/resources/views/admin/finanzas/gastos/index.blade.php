@extends('layouts.admin')

@section('page_title', 'Gastos')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Gastos</h3>
            <a href="{{ route('admin.finanzas.gastos.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg"></i> Nuevo gasto
            </a>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <select name="categoria" class="form-select">
                        <option value="">Todas las categorías</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->value }}" @selected(request('categoria') === $categoria->value)>{{ $categoria->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" name="desde" class="form-control" value="{{ request('desde') }}" placeholder="Desde">
                </div>
                <div class="col-md-3">
                    <input type="date" name="hasta" class="form-control" value="{{ request('hasta') }}" placeholder="Hasta">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary w-100">Filtrar</button>
                </div>
            </form>

            <p class="text-muted">Total del filtro: <strong>Bs. {{ number_format((float) $totalFiltrado, 2) }}</strong> ({{ $gastos->total() }} gasto(s))</p>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Categoría</th>
                        <th>Descripción</th>
                        <th>Caso</th>
                        <th class="text-end">Monto</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($gastos as $gasto)
                        <tr>
                            <td>{{ $gasto->fecha->format('d/m/Y') }}</td>
                            <td><span class="badge text-bg-secondary">{{ $gasto->categoria->label() }}</span></td>
                            <td>{{ $gasto->descripcion ?? '—' }}</td>
                            <td>
                                @if ($gasto->proceso)
                                    <a href="{{ route('admin.procesos.show', $gasto->proceso) }}">
                                        {{ $gasto->proceso->cliente->nombre }} {{ $gasto->proceso->cliente->apellidos }}
                                    </a>
                                @else
                                    <span class="text-muted">General del despacho</span>
                                @endif
                            </td>
                            <td class="text-end">Bs. {{ number_format((float) $gasto->monto, 2) }}</td>
                            <td class="text-end">
                                @if ($gasto->comprobante)
                                    <a href="{{ route('admin.finanzas.gastos.comprobante', $gasto) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download"></i>
                                    </a>
                                @endif
                                <form action="{{ route('admin.finanzas.gastos.destroy', $gasto) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('¿Eliminar este gasto? No se puede deshacer.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Sin gastos todavía.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($gastos->hasPages())
            <div class="card-footer">{{ $gastos->links() }}</div>
        @endif
    </div>
@stop
