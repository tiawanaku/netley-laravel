@extends('layouts.staff')

@section('page_title', 'Mis Casos')

@section('content')
    <div class="card">
        <div class="card-header"><h3 class="card-title">Mis Casos</h3></div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Materia legal</th>
                        <th>Tipo de proceso</th>
                        <th>Etapa actual</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($procesos as $proceso)
                        @php $etapaActual = $proceso->etapaActual(); @endphp
                        <tr>
                            <td>{{ $proceso->cliente->nombre }} {{ $proceso->cliente->apellidos }}</td>
                            <td>{{ $proceso->materiaLegal?->nombre ?? '—' }}</td>
                            <td>{{ $proceso->tipo_proceso }}</td>
                            <td>
                                @if ($etapaActual)
                                    <span class="badge text-bg-primary">{{ $etapaActual->etapa }}</span>
                                @else
                                    <span class="text-muted">Sin informar</span>
                                @endif
                            </td>
                            <td><span class="badge text-bg-info">{{ $proceso->estado->label() }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('staff.procesos.show', $proceso) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Sin casos asignados todavía.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($procesos->hasPages())
            <div class="card-footer">{{ $procesos->links() }}</div>
        @endif
    </div>
@stop
