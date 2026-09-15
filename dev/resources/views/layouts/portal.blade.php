@php
config([
    'adminlte.dashboard_url' => route('portal.dashboard'),
    'adminlte.logout_url' => route('portal.logout'),
    'adminlte.logout_method' => 'POST',
    'adminlte.menu' => [
        ['text' => 'Mis Procesos', 'url' => route('portal.dashboard'), 'icon' => 'bi bi-briefcase'],
    ],
]);
@endphp
@extends('adminlte::page')

@section('title', 'Netley · Portal Cliente')

@section('content_header')
    <h1>@yield('page_title', 'Portal del Cliente')</h1>
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
