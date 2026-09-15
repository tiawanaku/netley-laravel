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

        $proceso->load(['cliente', 'materiaLegal', 'abogado', 'etapas.personal']);

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
}
