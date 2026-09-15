@php
config([
    'adminlte.dashboard_url' => route('admin.dashboard'),
    'adminlte.logout_url' => route('admin.logout'),
    'adminlte.logout_method' => 'POST',
    'adminlte.menu' => [
        ['text' => 'Dashboard', 'url' => route('admin.dashboard'), 'icon' => 'bi bi-speedometer2'],
        ['header' => 'Operación'],
        ['text' => 'Consultas', 'url' => route('admin.consultas.index'), 'icon' => 'bi bi-chat-left-text'],
        ['text' => 'Cliente Ejecutivo', 'url' => route('admin.clientes.index'), 'icon' => 'bi bi-people'],
        ['text' => 'Agenda', 'url' => route('admin.dashboard'), 'icon' => 'bi bi-calendar3'],
        ['header' => 'Administración'],
        ['text' => 'Personal', 'url' => route('admin.personal.index'), 'icon' => 'bi bi-person-badge'],
        [
            'text' => 'Catálogos',
            'icon' => 'bi bi-collection',
            'submenu' => [
                ['text' => 'Materias Legales', 'url' => route('admin.catalogos.index', 'materias-legales')],
                ['text' => 'Orígenes', 'url' => route('admin.catalogos.index', 'origenes')],
                ['text' => 'Departamentos', 'url' => route('admin.catalogos.index', 'departamentos')],
            ],
        ],
    ],
]);
@endphp
@extends('adminlte::page')

@section('title', 'Netley · Admin')

@section('content_header')
    <h1>@yield('page_title', 'Panel Admin')</h1>
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif
@stop

@section('content')
    @yield('content')
@stop
