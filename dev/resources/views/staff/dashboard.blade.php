@extends('layouts.staff')

@section('page_title', 'Mi Agenda')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Bienvenido(a), {{ $personal->nombre }}</h3>
        </div>
        <div class="card-body">
            <p><strong>Rol:</strong> {{ $personal->rol->label() }}</p>
            <p><strong>Citas/llamadas de hoy:</strong> {{ $agendaHoy->count() }}</p>
            <p class="mb-0"><strong>Casos activos {{ $personal->esAdministrador() ? '(todos)' : 'asignados' }}:</strong> {{ $misProcesos }}</p>
        </div>
    </div>
@stop
