<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CategoriaRespuesta;
use App\Enums\EstadoAgenda;
use App\Enums\EstadoConsulta;
use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Cliente;
use App\Models\Consulta;
use App\Models\Delito;
use App\Models\Departamento;
use App\Models\MateriaLegal;
use App\Models\Origen;
use App\Models\Personal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsultaController extends Controller
{
    /**
     * Duración fija de cada cita/llamada y horario laboral disponible para
     * agendar (08:00 a 17:00, en bloques de 15 minutos).
     */
    protected const DURACION_MINUTOS = 15;

    protected const HORA_INICIO = 8;

    protected const HORA_FIN = 17;

    public function index(Request $request): View
    {
        $consultas = Consulta::query()
            ->when($request->filled('buscar'), function ($query) use ($request) {
                $termino = $request->string('buscar');
                $query->where(function ($q) use ($termino) {
                    $q->where('nombre', 'like', "%{$termino}%")
                        ->orWhere('apellido_paterno', 'like', "%{$termino}%")
                        ->orWhere('ci', 'like', "%{$termino}%")
                        ->orWhere('telefono', 'like', "%{$termino}%");
                });
            })
            ->when($request->filled('estado'), fn ($query) => $query->where('estado', $request->string('estado')))
            ->with(['materiaLegal', 'origen', 'departamento', 'cliente'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.consultas.index', [
            'consultas' => $consultas,
            'estados' => EstadoConsulta::cases(),
        ]);
    }

    public function create(): View
    {
        return view('admin.consultas.form', [
            'registro' => new Consulta(),
            ...$this->opciones(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validar($request);

        Consulta::create($data);

        return redirect()->route('admin.consultas.index')->with('status', 'Consulta registrada correctamente.');
    }

    public function show(Consulta $consulta): View
    {
        $consulta->load(['materiaLegal', 'origen', 'departamento', 'atendioPor', 'cliente', 'agendas.responsable']);

        // "Responder" solo aplica a la cita (no a la llamada) — debe coincidir
        // exactamente con lo que exige darRespuesta().
        $ultimaCita = $consulta->agendas()->where('tipo', 'cita')->latest('fecha_inicio')->first();

        return view('admin.consultas.show', [
            'consulta' => $consulta,
            'respuestas' => $consulta->respuestas(),
            'personal' => Personal::query()->where('estado', 'habilitado')->orderBy('nombre')->get(),
            'materiasLegales' => MateriaLegal::query()->where('activo', true)->orderBy('orden')->get(),
            'categoriasRespuesta' => CategoriaRespuesta::cases(),
            // El legado bloquea "Agendar" si ya hay una cita, y "Responder" si
            // esa cita ya tiene respuesta (una sola respuesta por consulta).
            'puedeAgendarCita' => ! $consulta->agendas()->where('tipo', 'cita')->exists(),
            'puedeAgendarLlamada' => ! $consulta->agendas()->where('tipo', 'llamada')->exists(),
            'puedeResponder' => $ultimaCita && ! $ultimaCita->respuestas()->exists(),
        ]);
    }

    public function edit(Consulta $consulta): View
    {
        return view('admin.consultas.form', [
            'registro' => $consulta,
            ...$this->opciones(),
        ]);
    }

    public function update(Request $request, Consulta $consulta): RedirectResponse
    {
        if ($consulta->estado === EstadoConsulta::ClienteEjecutivo) {
            abort(403, 'Una consulta ya convertida a Cliente Ejecutivo no puede editarse.');
        }

        $consulta->update($this->validar($request, $consulta->id));

        return redirect()->route('admin.consultas.index')->with('status', 'Consulta actualizada correctamente.');
    }

    public function destroy(Consulta $consulta): RedirectResponse
    {
        if ($consulta->cliente()->exists()) {
            return back()->withErrors(['consulta' => 'No se puede eliminar: esta consulta ya tiene un Cliente Ejecutivo asociado.']);
        }

        $consulta->delete();

        return redirect()->route('admin.consultas.index')->with('status', 'Consulta eliminada correctamente.');
    }

    public function agendarCita(Request $request, Consulta $consulta): RedirectResponse
    {
        return $this->agendar($request, $consulta, 'cita');
    }

    public function agendarLlamada(Request $request, Consulta $consulta): RedirectResponse
    {
        return $this->agendar($request, $consulta, 'llamada');
    }

    protected function agendar(Request $request, Consulta $consulta, string $tipo): RedirectResponse
    {
        if ($consulta->agendas()->where('tipo', $tipo)->exists()) {
            $mensaje = $tipo === 'cita' ? 'Esta consulta ya tiene una cita agendada.' : 'Esta consulta ya tiene una llamada agendada.';

            return back()->withErrors(['responsable_id' => $mensaje]);
        }

        $data = $request->validate([
            'responsable_id' => ['required', 'exists:personal,id'],
            'fecha' => ['required', 'date'],
            'hora' => ['required', 'date_format:H:i', $this->reglaHoraValida()],
            'modalidad' => ['required', 'in:presencial,virtual'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
        ]);

        $fechaInicio = \Carbon\Carbon::parse($data['fecha'].' '.$data['hora']);
        $fechaFin = $fechaInicio->copy()->addMinutes(self::DURACION_MINUTOS);

        if (Agenda::hayConflicto((int) $data['responsable_id'], $fechaInicio, $fechaFin)) {
            return back()->withErrors(['hora' => 'El responsable ya tiene una cita en ese horario. Elige otro.'])->withInput();
        }

        Agenda::create([
            'responsable_id' => $data['responsable_id'],
            'modalidad' => $data['modalidad'],
            'ubicacion' => $data['ubicacion'] ?? null,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'duracion_minutos' => self::DURACION_MINUTOS,
            'tipo' => $tipo,
            'estado' => EstadoAgenda::Pendiente->value,
            'consulta_id' => $consulta->id,
            'asunto' => ($tipo === 'cita' ? 'Cita' : 'Llamada').' — '.$consulta->nombre.' '.$consulta->apellido_paterno,
            'created_by' => $request->user('web')->id,
        ]);

        if ($tipo === 'cita') {
            $consulta->update(['estado' => EstadoConsulta::Agendada]);
        }

        $mensaje = $tipo === 'cita' ? 'Cita agendada correctamente.' : 'Llamada agendada correctamente.';

        return redirect()->route('admin.consultas.show', $consulta)->with('status', $mensaje);
    }

    /**
     * Horarios de 15 minutos disponibles para un responsable en una fecha,
     * excluyendo los que ya tiene ocupados (agendas no canceladas).
     */
    public function horariosDisponibles(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'responsable_id' => ['required', 'exists:personal,id'],
            'fecha' => ['required', 'date'],
        ]);

        $fecha = \Carbon\Carbon::parse($data['fecha'])->startOfDay();

        $ocupados = Agenda::query()
            ->where('responsable_id', $data['responsable_id'])
            ->whereNotIn('estado', [EstadoAgenda::Cancelada->value])
            ->whereDate('fecha_inicio', $fecha->toDateString())
            ->get(['fecha_inicio', 'fecha_fin']);

        $ahora = now();
        $horarios = [];

        for ($hora = self::HORA_INICIO; $hora < self::HORA_FIN; $hora++) {
            foreach ([0, 15, 30, 45] as $minuto) {
                $inicio = $fecha->copy()->setTime($hora, $minuto);
                $fin = $inicio->copy()->addMinutes(self::DURACION_MINUTOS);

                if ($inicio->lt($ahora)) {
                    continue;
                }

                $ocupado = $ocupados->contains(
                    fn ($agenda) => $agenda->fecha_inicio->lt($fin) && $agenda->fecha_fin->gt($inicio)
                );

                if (! $ocupado) {
                    $horarios[] = $inicio->format('H:i');
                }
            }
        }

        return response()->json($horarios);
    }

    protected function reglaHoraValida(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            if (! preg_match('/^(\d{2}):(\d{2})$/', $value, $m)) {
                return;
            }

            [$hora, $minuto] = [(int) $m[1], (int) $m[2]];

            if ($hora < self::HORA_INICIO || $hora >= self::HORA_FIN || ! in_array($minuto, [0, 15, 30, 45], true)) {
                $fail('Selecciona un horario válido, en intervalos de 15 minutos entre '.sprintf('%02d:00', self::HORA_INICIO).' y '.sprintf('%02d:00', self::HORA_FIN).'.');
            }
        };
    }

    public function darRespuesta(Request $request, Consulta $consulta): RedirectResponse
    {
        $data = $request->validate([
            'respuesta' => ['required', 'string'],
            'categoria' => ['required', 'in:Legal,Psicologica,Trabajo Social,Informatica,Otro'],
            'materia_legal_id' => ['nullable', 'required_if:categoria,Legal', 'exists:materias_legales,id'],
            'delito_id' => ['nullable', 'exists:delitos,id'],
            'publicar' => ['nullable', 'boolean'],
        ]);

        $agenda = $consulta->agendas()->where('tipo', 'cita')->latest('fecha_inicio')->first();

        if (! $agenda) {
            return back()->withErrors(['respuesta' => 'La consulta no tiene una cita agendada todavía.']);
        }

        if ($agenda->respuestas()->exists()) {
            return back()->withErrors(['respuesta' => 'Esta consulta ya tiene una respuesta registrada.']);
        }

        $agenda->respuestas()->create([
            'respuesta' => $data['respuesta'],
            'categoria' => $data['categoria'],
            'materia_legal_id' => $data['categoria'] === 'Legal' ? ($data['materia_legal_id'] ?? null) : null,
            'delito_id' => $data['categoria'] === 'Legal' ? ($data['delito_id'] ?? null) : null,
            'publicar' => $request->boolean('publicar'),
            'user_id' => $request->user('web')->id,
        ]);

        return redirect()->route('admin.consultas.show', $consulta)->with('status', 'Respuesta registrada correctamente.');
    }

    public function convertir(Consulta $consulta): RedirectResponse
    {
        if ($consulta->cliente()->exists()) {
            return back()->withErrors(['consulta' => 'Esta consulta ya tiene un Cliente Ejecutivo asociado.']);
        }

        [$cliente, $temporal] = Cliente::convertirDesdeConsulta($consulta);
        $consulta->update(['estado' => EstadoConsulta::ClienteEjecutivo]);

        // Prellenar el wizard de "Nuevo caso" con la materia legal/delito de la
        // última respuesta Legal — equivalente a ascender_caso.php del legado.
        $respuestaLegal = $consulta->ultimaRespuestaLegal();

        $rutaCaso = route('admin.procesos.create', $cliente).($respuestaLegal
            ? '?'.http_build_query([
                'materia_legal_id' => $respuestaLegal->materia_legal_id,
                'tipo_proceso' => $respuestaLegal->delito?->delito,
            ])
            : '');

        return redirect($rutaCaso)
            ->with('status', "Convertido a Cliente Ejecutivo. Usuario: {$cliente->usuario} — Contraseña temporal: {$temporal} (se muestra una sola vez). Complete los datos del caso.");
    }

    protected function validar(Request $request, ?int $ignorarId = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'ci' => ['nullable', 'string', 'max:255'],
            'telefono' => ['required', 'regex:/^[2-7][0-9]{6,7}$/'],
            'whatsapp' => ['nullable', 'regex:/^[+]?[0-9]{6,30}$/'],
            'departamento_id' => ['nullable', 'exists:departamentos,id'],
            'provincia' => ['nullable', 'string', 'max:255'],
            'pais' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'materia_legal_id' => ['nullable', 'exists:materias_legales,id'],
            'descripcion' => ['required', 'string'],
            'nota_interna' => ['nullable', 'string'],
            'origen_id' => ['nullable', 'exists:origenes,id'],
            'colegio_otros' => ['nullable', 'string', 'max:255'],
            'origen_otro' => ['nullable', 'string', 'max:255'],
        ], [
            'telefono.regex' => 'El teléfono debe tener 7-8 dígitos y empezar entre 2 y 7.',
            'whatsapp.regex' => 'El WhatsApp debe ser solo números (opcionalmente con "+" al inicio).',
        ]);
    }

    protected function opciones(): array
    {
        return [
            'departamentos' => Departamento::query()->where('activo', true)->orderBy('orden')->get(),
            'materiasLegales' => MateriaLegal::query()->where('activo', true)->orderBy('orden')->get(),
            'origenes' => Origen::query()->where('activo', true)->orderBy('orden')->get(),
        ];
    }
}
