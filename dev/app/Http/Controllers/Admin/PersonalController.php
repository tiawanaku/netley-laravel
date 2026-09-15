<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Cargo;
use App\Enums\EspecialidadAbogado;
use App\Enums\EspecialidadMedica;
use App\Enums\EspecialidadPsicologia;
use App\Enums\EspecialidadTrabajoSocial;
use App\Enums\EstadoCivil;
use App\Enums\EstadoPersonal;
use App\Enums\Expedido;
use App\Enums\Genero;
use App\Enums\ProfesionPersonal;
use App\Enums\RolPersonal;
use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Personal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PersonalController extends Controller
{
    public function index(Request $request): View
    {
        $personal = Personal::query()
            ->when($request->filled('buscar'), function ($query) use ($request) {
                $termino = $request->string('buscar');
                $query->where(function ($q) use ($termino) {
                    $q->where('nombre', 'like', "%{$termino}%")
                        ->orWhere('apellido_paterno', 'like', "%{$termino}%")
                        ->orWhere('apellido_materno', 'like', "%{$termino}%")
                        ->orWhere('ci', 'like', "%{$termino}%")
                        ->orWhere('cargo', 'like', "%{$termino}%")
                        ->orWhere('telefono', 'like', "%{$termino}%");
                });
            })
            ->with('departamento')
            ->orderBy('apellido_paterno')
            ->paginate(15)
            ->withQueryString();

        return view('admin.personal.index', ['personal' => $personal]);
    }

    public function create(): View
    {
        return view('admin.personal.form', [
            'registro' => new Personal(),
            ...$this->opciones(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validar($request);

        $personal = Personal::create($data);
        $temporal = $personal->generarAccesoPortal();

        return redirect()->route('admin.personal.index')
            ->with('status', "Personal creado. Usuario: {$personal->usuario} — Contraseña temporal: {$temporal} (se muestra una sola vez).");
    }

    public function edit(Personal $personal): View
    {
        return view('admin.personal.form', [
            'registro' => $personal,
            ...$this->opciones(),
        ]);
    }

    public function update(Request $request, Personal $personal): RedirectResponse
    {
        $data = $this->validar($request, $personal->id);

        $personal->update($data);

        return redirect()->route('admin.personal.index')->with('status', 'Personal actualizado correctamente.');
    }

    public function destroy(Personal $personal): RedirectResponse
    {
        $personal->delete();

        return redirect()->route('admin.personal.index')->with('status', 'Personal eliminado correctamente.');
    }

    public function resetAcceso(Personal $personal): RedirectResponse
    {
        $temporal = $personal->generarAccesoPortal();

        return redirect()->route('admin.personal.index')
            ->with('status', "Acceso reseteado. Usuario: {$personal->usuario} — Contraseña temporal: {$temporal} (se muestra una sola vez).");
    }

    protected function validar(Request $request, ?int $ignorarId = null): array
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'ci' => ['required', 'string', 'max:255', Rule::unique('personal', 'ci')->ignore($ignorarId)],
            'expedido' => ['nullable', Rule::enum(Expedido::class)],
            'expedido_otro' => ['nullable', 'string', 'max:255', 'required_if:expedido,'.Expedido::Otro->value],
            'genero' => ['required', Rule::enum(Genero::class)],
            'fecha_nacimiento' => ['required', 'date'],
            'nacionalidad' => ['required', 'string', 'max:255'],
            'estado_civil' => ['required', Rule::enum(EstadoCivil::class)],
            'cargo' => ['nullable', Rule::enum(Cargo::class)],
            'profesiones' => ['required', 'array', 'min:1'],
            'profesiones.*' => [Rule::enum(ProfesionPersonal::class)],
            'profesiones_otro' => ['nullable', 'string', 'max:255'],
            'especialidades' => ['nullable', 'array'],
            'especialidades.*' => ['string'],
            'telefono' => ['required', 'regex:/^[2-7][0-9]{6,7}$/'],
            'whatsapp' => ['nullable', 'regex:/^[+]?[0-9]{6,30}$/'],
            'email' => ['required', 'email', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'departamento_id' => ['nullable', 'exists:departamentos,id'],
            'numero_contrato' => ['nullable', 'string', 'max:255', Rule::unique('personal', 'numero_contrato')->ignore($ignorarId)],
            'estado' => ['required', Rule::enum(EstadoPersonal::class)],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'rol' => ['required', Rule::enum(RolPersonal::class)],
            'nota' => ['nullable', 'string'],
        ], [
            'telefono.regex' => 'El teléfono debe tener 7-8 dígitos y empezar entre 2 y 7.',
            'whatsapp.regex' => 'El WhatsApp debe ser solo números (opcionalmente con "+" al inicio).',
            'profesiones.required' => 'Seleccione al menos una profesión.',
        ]);

        // Las especialidades válidas dependen de qué profesiones se marcaron
        // (Abogado → legales, Psicologia → psicológicas, etc — ver formulario
        // legado en requerimientos/).
        $gruposPermitidos = [];
        if (in_array(ProfesionPersonal::Abogado->value, $data['profesiones'], true)) {
            $gruposPermitidos = [...$gruposPermitidos, ...array_column(EspecialidadAbogado::cases(), 'value')];
        }
        if (in_array(ProfesionPersonal::Psicologia->value, $data['profesiones'], true)) {
            $gruposPermitidos = [...$gruposPermitidos, ...array_column(EspecialidadPsicologia::cases(), 'value')];
        }
        if (in_array(ProfesionPersonal::Medico->value, $data['profesiones'], true)) {
            $gruposPermitidos = [...$gruposPermitidos, ...array_column(EspecialidadMedica::cases(), 'value')];
        }
        if (in_array(ProfesionPersonal::TrabajoSocial->value, $data['profesiones'], true)) {
            $gruposPermitidos = [...$gruposPermitidos, ...array_column(EspecialidadTrabajoSocial::cases(), 'value')];
        }

        $data['especialidades'] = array_values(array_intersect($data['especialidades'] ?? [], $gruposPermitidos));

        return $data;
    }

    protected function opciones(): array
    {
        return [
            'departamentos' => Departamento::query()->where('activo', true)->orderBy('orden')->get(),
            'generos' => Genero::cases(),
            'estadosCiviles' => EstadoCivil::cases(),
            'estadosPersonal' => EstadoPersonal::cases(),
            'roles' => RolPersonal::cases(),
            'cargos' => Cargo::cases(),
            'profesionesDisponibles' => ProfesionPersonal::cases(),
            'expedidos' => Expedido::cases(),
            'especialidadesLegales' => EspecialidadAbogado::cases(),
            'especialidadesPsicologia' => EspecialidadPsicologia::cases(),
            'especialidadesMedicas' => EspecialidadMedica::cases(),
            'especialidadesTrabajoSocial' => EspecialidadTrabajoSocial::cases(),
        ];
    }
}
