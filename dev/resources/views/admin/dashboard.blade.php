@extends('layouts.admin')

@section('page_title', 'Dashboard')

@section('content')
    <div class="row">
        <div class="col-md-3 col-sm-6 col-12">
            <div class="small-box text-bg-primary">
                <div class="inner">
                    <h3>{{ $totalConsultas }}</h3>
                    <p>Consultas</p>
                </div>
                <span class="small-box-icon"><i class="bi bi-chat-left-text"></i></span>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="small-box text-bg-success">
                <div class="inner">
                    <h3>{{ $totalClientes }}</h3>
                    <p>Clientes Ejecutivos</p>
                </div>
                <span class="small-box-icon"><i class="bi bi-people"></i></span>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="small-box text-bg-warning">
                <div class="inner">
                    <h3>{{ $procesosActivos }}</h3>
                    <p>Casos activos</p>
                </div>
                <span class="small-box-icon"><i class="bi bi-briefcase"></i></span>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="small-box text-bg-info">
                <div class="inner">
                    <h3>{{ $agendaHoy }}</h3>
                    <p>Agenda de hoy</p>
                </div>
                <span class="small-box-icon"><i class="bi bi-calendar3"></i></span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Calendario de Agenda</h3>
            <div class="card-tools d-flex align-items-center gap-3">
                <span><span class="badge rounded-pill" style="background-color:#0d6efd;">&nbsp;</span> Consulta</span>
                <span><span class="badge rounded-pill" style="background-color:#28a745;">&nbsp;</span> Cliente Ejecutivo</span>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="filtro-responsable-texto" class="form-label">Filtrar por responsable</label>
                    <div class="input-group">
                        <input
                            type="text"
                            id="filtro-responsable-texto"
                            class="form-control"
                            placeholder="Escribe el nombre del abogado/a..."
                            list="lista-personal"
                            autocomplete="off"
                        >
                        <button type="button" id="filtro-responsable-limpiar" class="btn btn-outline-secondary" title="Ver todo el personal">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <datalist id="lista-personal">
                        @foreach ($personal as $p)
                            <option data-id="{{ $p->id }}" value="{{ $p->nombre }} {{ $p->apellidos }}"></option>
                        @endforeach
                    </datalist>
                    <input type="hidden" id="filtro-responsable" value="">
                </div>
            </div>
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
            var filtroResponsable = document.getElementById('filtro-responsable');
            var filtroTexto = document.getElementById('filtro-responsable-texto');
            var filtroLimpiar = document.getElementById('filtro-responsable-limpiar');
            var listaPersonal = document.getElementById('lista-personal');
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
                events: function (info, successCallback, failureCallback) {
                    var url = new URL('{{ route('admin.agenda.eventos') }}');
                    if (filtroResponsable && filtroResponsable.value) {
                        url.searchParams.set('responsable_id', filtroResponsable.value);
                    }
                    fetch(url)
                        .then(function (r) { return r.json(); })
                        .then(successCallback)
                        .catch(failureCallback);
                },
            });

            calendar.render();

            // Busca en la <datalist> el <option> cuyo texto coincide exactamente
            // con lo escrito, y devuelve el id del responsable que guarda en
            // data-id. Mientras el texto no calce con ningún nombre completo
            // (el usuario sigue escribiendo) se deja el filtro como está.
            function idPorNombreEscrito(texto) {
                if (!listaPersonal) return null;
                var opciones = listaPersonal.querySelectorAll('option');
                var buscado = texto.trim().toLowerCase();
                for (var i = 0; i < opciones.length; i++) {
                    if (opciones[i].value.trim().toLowerCase() === buscado) {
                        return opciones[i].getAttribute('data-id');
                    }
                }
                return null;
            }

            if (filtroTexto && filtroResponsable) {
                filtroTexto.addEventListener('input', function () {
                    var texto = filtroTexto.value;

                    if (texto.trim() === '') {
                        filtroResponsable.value = '';
                        calendar.refetchEvents();
                        return;
                    }

                    var id = idPorNombreEscrito(texto);
                    if (id) {
                        filtroResponsable.value = id;
                        calendar.refetchEvents();
                    }
                });
            }

            if (filtroLimpiar && filtroTexto && filtroResponsable) {
                filtroLimpiar.addEventListener('click', function () {
                    filtroTexto.value = '';
                    filtroResponsable.value = '';
                    calendar.refetchEvents();
                });
            }
        });
    </script>
@endpush
