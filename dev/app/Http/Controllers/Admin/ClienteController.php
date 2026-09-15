<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TipoPago;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Delito;
use App\Models\Finanza;
use App\Models\MateriaLegal;
use App\Models\Personal;
use App\Models\Proceso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(Request $request): View
    {
        $clientes = Cliente::query()
            ->when($request->filled('buscar'), function ($query) use ($request) {
                $termino = $request->string('buscar');
                $query->where(function ($q) use ($termino) {
                    $q->where('nombre', 'like', "%{$termino}%")
                        ->orWhere('apellidos', 'like', "%{$termino}%")
                        ->orWhere('ci', 'like', "%{$termino}%");
                });
            })
            ->withCount('procesos')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.clientes.index', ['clientes' => $clientes]);
    }

    public function create(): View
    {
        return view('admin.clientes.form', $this->opciones());
    }

    /**
     * Alta directa: crea Cliente + Proceso + Finanza (y PlanPago si se
     * indicaron cuotas) en una sola operación — equivalente a
     * CreaClienteEjecutivoDirecto del proyecto original.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'ci' => ['nullable', 'string', 'max:255'],
            'telefono' => ['required', 'regex:/^[2-7][0-9]{6,7}$/'],
            'whatsapp' => ['nullable', 'regex:/^[+]?[0-9]{6,30}$/'],

            'materia_legal_id' => ['required', 'exists:materias_legales,id'],
            'tipo_proceso' => ['required', 'string', 'max:255'],
            'tiempo_proceso_meses' => ['required', 'integer', 'min:1'],
            'abogado_id' => ['required', 'exists:personal,id'],

            'costo' => ['required', 'numeric', 'min:0'],
            'tipo_pago' => ['required', 'in:semanal,mensual,al_contado'],
            'anticipo' => ['nullable', 'numeric', 'min:0'],
            'cuotas' => ['nullable', 'integer', 'min:1'],
            'fecha_primera_cuota' => ['nullable', 'date', 'required_with:cuotas'],
        ], [
            'telefono.regex' => 'El teléfono debe tener 7-8 dígitos y empezar entre 2 y 7.',
            'whatsapp.regex' => 'El WhatsApp debe ser solo números (opcionalmente con "+" al inicio).',
        ]);

        [$cliente, $proceso, $temporal] = DB::transaction(function () use ($data) {
            [$cliente, $temporal] = Cliente::crearDirecto($data);

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

            return [$cliente, $proceso, $temporal];
        });

        return redirect()->route('admin.procesos.show', $proceso)
            ->with('status', "Cliente Ejecutivo creado. Usuario: {$cliente->usuario} — Contraseña temporal: {$temporal} (se muestra una sola vez).");
    }

    public function show(Cliente $cliente): View
    {
        $cliente->load(['procesos.materiaLegal', 'procesos.abogado', 'consulta']);

        return view('admin.clientes.show', ['cliente' => $cliente]);
    }

    public function edit(Cliente $cliente): View
    {
        return view('admin.clientes.edit', ['cliente' => $cliente]);
    }

    public function update(Request $request, Cliente $cliente): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'ci' => ['nullable', 'string', 'max:255'],
            'telefono' => ['required', 'regex:/^[2-7][0-9]{6,7}$/'],
            'whatsapp' => ['nullable', 'regex:/^[+]?[0-9]{6,30}$/'],
        ], [
            'telefono.regex' => 'El teléfono debe tener 7-8 dígitos y empezar entre 2 y 7.',
            'whatsapp.regex' => 'El WhatsApp debe ser solo números (opcionalmente con "+" al inicio).',
        ]);

        $cliente->update($data);

        return redirect()->route('admin.clientes.show', $cliente)->with('status', 'Datos actualizados correctamente.');
    }

    public function delitosPorMateria(Request $request)
    {
        $materiaLegalId = $request->integer('materia_legal_id');

        return response()->json(Delito::opcionesPara($materiaLegalId));
    }

    public function abogadosPorMateria(Request $request)
    {
        $materia = MateriaLegal::find($request->integer('materia_legal_id'));

        $abogados = Personal::query()
            ->where('rol', 'abogado')
            ->where('estado', 'habilitado')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno', 'especialidades'])
            ->when($materia, fn ($coleccion) => $coleccion->filter(fn (Personal $p) => $p->tieneEspecialidadEnMateria($materia)))
            ->map(fn (Personal $p) => [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'apellidos' => $p->apellidos,
            ])
            ->values();

        return response()->json($abogados);
    }

    protected function opciones(): array
    {
        return [
            'materiasLegales' => MateriaLegal::query()->where('activo', true)->orderBy('orden')->get(),
            'tiposPago' => TipoPago::cases(),
        ];
    }
}
