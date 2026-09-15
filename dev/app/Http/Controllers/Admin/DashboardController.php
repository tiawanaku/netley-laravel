<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoAgenda;
use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Cliente;
use App\Models\Consulta;
use App\Models\Personal;
use App\Models\Proceso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Colores del calendario de Agenda: azul para citas que todavía son de
     * una Consulta (lead), verde para las que ya pertenecen a un Cliente
     * Ejecutivo (consulta convertida, o agenda ligada directo a cliente/caso).
     */
    protected const COLOR_CONSULTA = '#0d6efd';

    protected const COLOR_CLIENTE_EJECUTIVO = '#28a745';

    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'totalConsultas' => Consulta::count(),
            'totalClientes' => Cliente::count(),
            'procesosActivos' => Proceso::where('estado', 'activo')->count(),
            'agendaHoy' => Agenda::deHoy()->count(),
            'personal' => Personal::query()->where('estado', 'habilitado')->orderBy('nombre')->get(),
        ]);
    }

    public function eventos(Request $request): JsonResponse
    {
        $agendas = Agenda::query()
            ->with('consulta.cliente')
            ->whereNotIn('estado', [EstadoAgenda::Cancelada->value])
            ->when($request->filled('responsable_id'), fn ($query) => $query->where('responsable_id', $request->integer('responsable_id')))
            ->get();

        $eventos = $agendas->map(function (Agenda $agenda) {
            $esClienteEjecutivo = $agenda->cliente_id !== null
                || $agenda->proceso_id !== null
                || $agenda->consulta?->cliente !== null;

            return [
                'id' => $agenda->id,
                'title' => $agenda->asunto ?: $agenda->tipo->label(),
                'start' => $agenda->fecha_inicio->toIso8601String(),
                'end' => $agenda->fecha_fin->toIso8601String(),
                'color' => $esClienteEjecutivo ? self::COLOR_CLIENTE_EJECUTIVO : self::COLOR_CONSULTA,
                'url' => $agenda->consulta_id ? route('admin.consultas.show', $agenda->consulta_id) : null,
            ];
        });

        return response()->json($eventos->values());
    }
}
