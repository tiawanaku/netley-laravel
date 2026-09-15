@extends('layouts.admin')

@section('page_title', 'Cliente: '.$cliente->nombre.' '.$cliente->apellidos)

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Datos de contacto</h3></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">CI</dt>
                        <dd class="col-7">{{ $cliente->ci ?? '—' }}</dd>
                        <dt class="col-5">Teléfono</dt>
                        <dd class="col-7">{{ $cliente->telefono }}</dd>
                        <dt class="col-5">WhatsApp</dt>
                        <dd class="col-7">{{ $cliente->whatsapp ?? '—' }}</dd>
                        <dt class="col-5">Usuario portal</dt>
                        <dd class="col-7"><code>{{ $cliente->usuario }}</code></dd>
                        <dt class="col-5">Origen</dt>
                        <dd class="col-7">{{ $cliente->consulta ? 'Desde consulta' : 'Alta directa' }}</dd>
                    </dl>
                    <a href="{{ route('admin.clientes.edit', $cliente) }}" class="btn btn-sm btn-outline-primary mt-2">Editar datos</a>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Casos</h3>
                    <a href="{{ route('admin.procesos.create', $cliente) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg"></i> Nuevo caso
                    </a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Materia legal</th>
                                <th>Tipo de proceso</th>
                                <th>Abogado</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cliente->procesos as $proceso)
                                <tr>
                                    <td>{{ $proceso->materiaLegal?->nombre ?? '—' }}</td>
                                    <td>{{ $proceso->tipo_proceso }}</td>
                                    <td>{{ $proceso->abogado?->nombre }} {{ $proceso->abogado?->apellidos }}</td>
                                    <td><span class="badge text-bg-info">{{ $proceso->estado->label() }}</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.procesos.show', $proceso) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Sin casos todavía.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
