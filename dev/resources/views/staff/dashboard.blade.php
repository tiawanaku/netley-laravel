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

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Mi Calendario</h3>
            <div class="card-tools d-flex align-items-center gap-3">
                <span><span class="badge rounded-pill" style="background-color:#0d6efd;">&nbsp;</span> Consulta</span>
                <span><span class="badge rounded-pill" style="background-color:#28a745;">&nbsp;</span> Cliente Ejecutivo</span>
            </div>
        </div>
        <div class="card-body">
            <div id="calendario-agenda"></div>
        </div>
    </div>
@stop

@push('css')
    <style>
        #calendario-agenda .fc-event { cursor: pointer; }
    </style>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales-all.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('calendario-agenda');
            if (!el || typeof FullCalendar === 'undefined') return;

            var calendar = new FullCalendar.Calendar(el, {
                locale: 'es',
                height: 'auto',
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
                },
                events: '{{ route('staff.agenda.eventos') }}',
            });

            calendar.render();
        });
    </script>
@endpush
