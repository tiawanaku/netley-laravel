@extends('layouts.admin')

@section('page_title', 'Recibos')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Recibos</h3>
            <a href="{{ route('admin.finanzas.recibos.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg"></i> Nuevo recibo
            </a>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <input type="text" name="buscar" class="form-control" placeholder="N° o concepto..." value="{{ request('buscar') }}">
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

            <p class="text-muted">Total del filtro: <strong>Bs. {{ number_format((float) $totalFiltrado, 2) }}</strong> ({{ $recibos->total() }} recibo(s))</p>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Fecha</th>
                        <th>Concepto</th>
                        <th>Tipo</th>
                        <th>Cliente</th>
                        <th class="text-end">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recibos as $recibo)
                        <tr>
                            <td><a href="{{ route('admin.finanzas.recibos.show', $recibo) }}">{{ $recibo->numero }}</a></td>
                            <td>{{ $recibo->fecha->format('d/m/Y') }}</td>
                            <td>{{ $recibo->concepto }}</td>
                            <td><span class="badge text-bg-secondary">{{ $recibo->tipo->label() }}</span></td>
                            <td>
                                @if ($recibo->cliente)
                                    {{ $recibo->cliente->nombre }} {{ $recibo->cliente->apellidos }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-end">Bs. {{ number_format((float) $recibo->monto, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Sin recibos todavía.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($recibos->hasPages())
            <div class="card-footer">{{ $recibos->links() }}</div>
        @endif
    </div>
@stop
