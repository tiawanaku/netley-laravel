<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Proceso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProcesoController extends Controller
{
    public function index(Request $request): View
    {
        $personal = $request->user('personal');

        $procesos = Proceso::query()
            ->with(['cliente', 'materiaLegal', 'etapas'])
            ->when(! $personal->esAdministrador(), fn ($query) => $query->where('abogado_id', $personal->id))
            ->latest()
            ->paginate(15);

        return view('staff.procesos.index', ['procesos' => $procesos]);
    }

    public function show(Request $request, Proceso $proceso): View
    {
        abort_unless($request->user('personal')->puedeVerCaso($proceso), 403);

        $proceso->load([
            'cliente', 'materiaLegal', 'abogado', 'etapas.personal', 'finanza',
            'ficha', 'gestionesExtrajudiciales.personal', 'recibos',
        ]);

        return view('staff.procesos.show', ['proceso' => $proceso]);
    }

    public function agregarEtapa(Request $request, Proceso $proceso): RedirectResponse
    {
        abort_unless($request->user('personal')->puedeVerCaso($proceso), 403);

        $data = $request->validate([
            'etapa' => ['required', 'string', 'max:255'],
            'comentario' => ['nullable', 'string'],
        ]);

        $proceso->etapas()->create([
            ...$data,
            'personal_id' => $request->user('personal')->id,
        ]);

        return redirect()->route('staff.procesos.show', $proceso)->with('status', 'Etapa registrada correctamente.');
    }

    /**
     * Ficha de seguimiento del caso: número de expediente, partes, fechas de
     * cierre y resultado. Es 1:1 por proceso — cada envío actualiza (o crea)
     * el mismo registro, no acumula historial como las etapas.
     */
    public function actualizarFicha(Request $request, Proceso $proceso): RedirectResponse
    {
        abort_unless($request->user('personal')->puedeVerCaso($proceso), 403);

        $data = $request->validate([
            'numero_caso' => ['nullable', 'string', 'max:255'],
            'denunciante' => ['nullable', 'string', 'max:255'],
            'denunciado' => ['nullable', 'string', 'max:255'],
            'fecha_inicio_caso' => ['nullable', 'date'],
            'fecha_finalizacion' => ['nullable', 'date'],
            'resultado' => ['nullable', 'string', 'max:2000'],
        ]);

        $proceso->ficha()->updateOrCreate(
            ['proceso_id' => $proceso->id],
            [...$data, 'actualizado_por' => $request->user('personal')->id],
        );

        return redirect()->route('staff.procesos.show', $proceso)->with('status', 'Ficha del caso actualizada.');
    }

    public function agregarGestion(Request $request, Proceso $proceso): RedirectResponse
    {
        abort_unless($request->user('personal')->puedeVerCaso($proceso), 403);

        $data = $request->validate([
            'fecha' => ['nullable', 'date'],
            'motivo' => ['nullable', 'string', 'max:255'],
            'fecha_devolucion' => ['nullable', 'date'],
        ]);

        $proceso->gestionesExtrajudiciales()->create([
            ...$data,
            'personal_id' => $request->user('personal')->id,
        ]);

        return redirect()->route('staff.procesos.show', $proceso)->with('status', 'Gestión extrajudicial registrada.');
    }
}
