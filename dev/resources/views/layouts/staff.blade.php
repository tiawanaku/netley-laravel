@php
config([
    'adminlte.dashboard_url' => route('staff.dashboard'),
    'adminlte.logout_url' => route('staff.logout'),
    'adminlte.logout_method' => 'POST',
    'adminlte.menu' => [
        ['text' => 'Mi Agenda', 'url' => route('staff.dashboard'), 'icon' => 'bi bi-calendar3'],
        ['text' => 'Consultas', 'url' => route('staff.dashboard'), 'icon' => 'bi bi-chat-left-text'],
        ['text' => 'Mis Casos', 'url' => route('staff.procesos.index'), 'icon' => 'bi bi-briefcase'],
    ],
]);
@endphp
@extends('adminlte::page')

@section('title', 'Netley · Staff')

@section('content_header')
    <h1>@yield('page_title', 'Panel de Personal')</h1>
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
