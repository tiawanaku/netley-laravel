@extends('layouts.admin')

@section('page_title', 'Finanzas — Resumen')

@section('content')
    <form method="GET" class="card mb-3">
        <div class="card-body d-flex flex-wrap align-items-end gap-2">
            <div>
                <label class="form-label mb-0">Desde</label>
                <input type="date" name="desde" class="form-control" value="{{ $desde->toDateString() }}">
            </div>
            <div>
                <label class="form-label mb-0">Hasta</label>
                <input type="date" name="hasta" class="form-control" value="{{ $hasta->toDateString() }}">
            </div>
            <button type="submit" class="btn btn-outline-primary">Filtrar</button>
        </div>
    </form>

    <div class="row">
        <div class="col-md-4 col-sm-6 col-12">
            <div class="small-box text-bg-success">
                <div class="inner">
                    <h3>Bs. {{ number_format((float) $totalRecibos, 2) }}</h3>
                    <p>Ingresos (recibos)</p>
                </div>
                <span class="small-box-icon"><i class="bi bi-receipt"></i></span>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-12">
            <div class="small-box text-bg-danger">
                <div class="inner">
                    <h3>Bs. {{ number_format((float) $totalGastos, 2) }}</h3>
                    <p>Gastos</p>
                </div>
                <span class="small-box-icon"><i class="bi bi-cash-coin"></i></span>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-12">
            <div class="small-box {{ $balance >= 0 ? 'text-bg-info' : 'text-bg-warning' }}">
                <div class="inner">
                    <h3>Bs. {{ number_format((float) $balance, 2) }}</h3>
                    <p>Balance neto del período</p>
                </div>
                <span class="small-box-icon"><i class="bi bi-calculator"></i></span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Últimos recibos</h3>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.finanzas.recibos.create') }}" class="btn btn-sm btn-success">
                            <i class="bi bi-plus-lg"></i> Nuevo
                        </a>
                        <a href="{{ route('admin.finanzas.recibos.index') }}" class="btn btn-sm btn-outline-secondary">Ver todos</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>N°</th><th>Fecha</th><th>Concepto</th><th class="text-end">Monto</th></tr></thead>
                        <tbody>
                            @forelse ($ultimosRecibos as $recibo)
                                <tr>
                                    <td><a href="{{ route('admin.finanzas.recibos.show', $recibo) }}">{{ $recibo->numero }}</a></td>
                                    <td>{{ $recibo->fecha->format('d/m/Y') }}</td>
                                    <td>{{ $recibo->concepto }}</td>
                                    <td class="text-end">Bs. {{ number_format((float) $recibo->monto, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Sin recibos todavía.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Últimos gastos</h3>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.finanzas.gastos.create') }}" class="btn btn-sm btn-success">
                            <i class="bi bi-plus-lg"></i> Nuevo
                        </a>
                        <a href="{{ route('admin.finanzas.gastos.index') }}" class="btn btn-sm btn-outline-secondary">Ver todos</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Fecha</th><th>Categoría</th><th>Caso</th><th class="text-end">Monto</th></tr></thead>
                        <tbody>
                            @forelse ($ultimosGastos as $gasto)
                                <tr>
                                    <td>{{ $gasto->fecha->format('d/m/Y') }}</td>
                                    <td>{{ $gasto->categoria->label() }}</td>
                                    <td>{{ $gasto->proceso?->tipo_proceso ?? '—' }}</td>
                                    <td class="text-end">Bs. {{ number_format((float) $gasto->monto, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Sin gastos todavía.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
