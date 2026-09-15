<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TipoPago;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Finanza;
use App\Models\MateriaLegal;
use App\Models\Personal;
use App\Models\Proceso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProcesoController extends Controller
{
    public function create(Request $request, Cliente $cliente): View
    {
        return view('admin.procesos.form', [
            'cliente' => $cliente,
            'materiasLegales' => MateriaLegal::query()->where('activo', true)->orderBy('orden')->get(),
            'tiposPago' => TipoPago::cases(),
            'materiaLegalPrefill' => $request->integer('materia_legal_id') ?: null,
            'tipoProcesoPrefill' => $request->string('tipo_proceso')->toString(),
        ]);
    }

    public function store(Request $request, Cliente $cliente): RedirectResponse
    {
        $data = $request->validate([
            'materia_legal_id' => ['required', 'exists:materias_legales,id'],
            'tipo_proceso' => ['required', 'string', 'max:255'],
            'tiempo_proceso_meses' => ['required', 'integer', 'min:1'],
            'abogado_id' => ['required', 'exists:personal,id'],
            'costo' => ['required', 'numeric', 'min:0'],
            'tipo_pago' => ['required', 'in:semanal,mensual,al_contado'],
            'anticipo' => ['nullable', 'numeric', 'min:0'],
            'cuotas' => ['nullable', 'integer', 'min:1'],
            'fecha_primera_cuota' => ['nullable', 'date', 'required_with:cuotas'],
        ]);

        $proceso = Proceso::create([
            'cliente_id' => $cliente->id,
            'materia_legal_id' => $data['materia_legal_id'],
            'tipo_proceso' => $data['tipo_proceso'],
            'tiempo_proceso_meses' => $data['tiempo_proceso_meses'],
            'abogado_id' => $data['abogado_id'],
        ]);

        $finanza = Finanza::create([
            'proceso_id' => $proceso->id,
            'costo' => $data['costo'],
            'tipo_pago' => $data['tipo_pago'],
            'anticipo' => $data['anticipo'] ?? 0,
            'anticipo_registrado_en' => ($data['anticipo'] ?? 0) > 0 ? now() : null,
        ]);

        if (! empty($data['cuotas'])) {
            $finanza->generarPlanPagos((int) $data['cuotas'], \Carbon\Carbon::parse($data['fecha_primera_cuota']));
        }

        return redirect()->route('admin.procesos.show', $proceso)->with('status', 'Caso creado correctamente.');
    }

    public function show(Proceso $proceso): View
    {
        $proceso->load(['cliente', 'materiaLegal', 'abogado', 'finanza.cuotas', 'agendas', 'documentos']);

        return view('admin.procesos.show', [
            'proceso' => $proceso,
            'timeline' => $proceso->timeline(),
        ]);
    }

    public function edit(Proceso $proceso): View
    {
        return view('admin.procesos.edit', [
            'proceso' => $proceso,
            'materiasLegales' => MateriaLegal::query()->where('activo', true)->orderBy('orden')->get(),
            // Incluye siempre al abogado ya asignado, aunque ya no cumpla el
            // filtro (rol distinto o deshabilitado) — si no, el <select> se
            // queda sin esa opción y guardar sin querer reasignaría el caso.
            'abogados' => Personal::query()
                ->where(fn ($q) => $q->where('rol', 'abogado')->where('estado', 'habilitado'))
                ->orWhere('id', $proceso->abogado_id)
                ->orderBy('nombre')
                ->get(),
        ]);
    }

    public function update(Request $request, Proceso $proceso): RedirectResponse
    {
        $data = $request->validate([
            'materia_legal_id' => ['required', 'exists:materias_legales,id'],
            'tipo_proceso' => ['required', 'string', 'max:255'],
            'tiempo_proceso_meses' => ['required', 'integer', 'min:1'],
            'abogado_id' => ['required', 'exists:personal,id'],
            'estado' => ['required', 'in:activo,cerrado,archivado'],
        ]);

        $proceso->update($data);

        return redirect()->route('admin.procesos.show', $proceso)->with('status', 'Caso actualizado correctamente.');
    }

    public function generarPlanPagos(Request $request, Proceso $proceso): RedirectResponse
    {
        $data = $request->validate([
            'cuotas' => ['required', 'integer', 'min:1'],
            'fecha_primera_cuota' => ['required', 'date'],
        ]);

        $proceso->finanza->generarPlanPagos((int) $data['cuotas'], \Carbon\Carbon::parse($data['fecha_primera_cuota']));

        return redirect()->route('admin.procesos.show', $proceso)->with('status', 'Plan de pagos generado correctamente.');
    }
}
