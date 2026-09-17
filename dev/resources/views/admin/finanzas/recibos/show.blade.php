@extends('layouts.admin')

@section('page_title', 'Recibo '.$recibo->numero)

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Recibo {{ $recibo->numero }}</h3>
                    <button type="button" class="btn btn-sm btn-outline-secondary d-print-none" onclick="window.print()">
                        <i class="bi bi-printer"></i> Imprimir
                    </button>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-4">N° de recibo</dt>
                        <dd class="col-8"><code>{{ $recibo->numero }}</code></dd>
                        <dt class="col-4">Fecha</dt>
                        <dd class="col-8">{{ $recibo->fecha->format('d/m/Y') }}</dd>
                        <dt class="col-4">Concepto</dt>
                        <dd class="col-8">{{ $recibo->concepto }}</dd>
                        <dt class="col-4">Tipo</dt>
                        <dd class="col-8"><span class="badge text-bg-secondary">{{ $recibo->tipo->label() }}</span></dd>
                        <dt class="col-4">Monto</dt>
                        <dd class="col-8 fs-4 fw-bold">Bs. {{ number_format((float) $recibo->monto, 2) }}</dd>
                        <dt class="col-4">Cliente</dt>
                        <dd class="col-8">
                            @if ($recibo->cliente)
                                {{ $recibo->cliente->nombre }} {{ $recibo->cliente->apellidos }}
                            @else
                                —
                            @endif
                        </dd>
                        <dt class="col-4">Caso</dt>
                        <dd class="col-8">
                            @if ($recibo->proceso)
                                <a href="{{ route('admin.procesos.show', $recibo->proceso) }}">{{ $recibo->proceso->tipo_proceso }}</a>
                            @else
                                —
                            @endif
                        </dd>
                        @if ($recibo->planPago)
                            <dt class="col-4">Cuota relacionada</dt>
                            <dd class="col-8">Vencimiento {{ $recibo->planPago->fecha->format('d/m/Y') }}</dd>
                        @endif
                        <dt class="col-4">Emitido por</dt>
                        <dd class="col-8">{{ $recibo->user?->name ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@stop
