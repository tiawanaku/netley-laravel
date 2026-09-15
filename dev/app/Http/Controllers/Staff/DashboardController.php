<?php

namespace App\Http\Controllers\Staff;

use App\Enums\EstadoAgenda;
use App\Http\Controllers\Controller;
use App\Models\Agenda;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected const COLOR_CONSULTA = '#0d6efd';

    protected const COLOR_CLIENTE_EJECUTIVO = '#28a745';

    public function __invoke(Request $request): View
    {
        $personal = $request->user('personal');

        return view('staff.dashboard', [
            'personal' => $personal,
            'agendaHoy' => $personal->agendas()->deHoy()->get(),
            'misProcesos' => $personal->esAdministrador()
                ? \App\Models\Proceso::where('estado', 'activo')->count()
                : $personal->procesos()->where('estado', 'activo')->count(),
        ]);
    }

    /**
     * Eventos del calendario de "Mi Agenda": solo las citas/llamadas donde el
     * personal logueado es el responsable.
     */
    public function eventos(Request $request): JsonResponse
    {
        $personal = $request->user('personal');

        $agendas = Agenda::query()
            ->with('consulta.cliente')
            ->where('responsable_id', $personal->id)
            ->whereNotIn('estado', [EstadoAgenda::Cancelada->value])
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
            ];
        });

        return response()->json($eventos->values());
    }
}
