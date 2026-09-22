<?php

namespace App\Http\Controllers;

use App\Models\Consulta;
use App\Models\Departamento;
use App\Models\MateriaLegal;
use App\Models\Origen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Front page pública — reemplaza al portal legacy (netley.site, CodeIgniter)
 * como punto de entrada de captación de consultas. Ver
 * docs/Front Page Legacy - Analisis para Reconstruccion.md para el análisis
 * del sitio original y las decisiones de diseño tomadas aquí.
 */
class PublicoController extends Controller
{
    public function index(Request $request): View
    {
        return view('publico.home', [
            'materiasLegales' => MateriaLegal::query()->where('activo', true)->orderBy('orden')->get(),
            'departamentos' => Departamento::query()->where('activo', true)->orderBy('orden')->get(),
            'captcha' => $this->generarCaptcha($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'telefono' => ['required', 'regex:/^[2-7][0-9]{6,7}$/'],
            'whatsapp' => ['nullable', 'regex:/^[+]?[0-9]{6,30}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'departamento_id' => ['nullable', 'exists:departamentos,id'],
            'materia_legal_id' => ['nullable', 'exists:materias_legales,id'],
            'descripcion' => ['required', 'string', 'max:1500'],
            'captcha' => ['required', 'string'],
        ], [
            'telefono.regex' => 'El celular debe tener 7-8 dígitos y empezar entre 2 y 7.',
            'whatsapp.regex' => 'El WhatsApp debe ser solo números (opcionalmente con "+" al inicio).',
            'descripcion.max' => 'La consulta no debe superar los 1500 caracteres.',
        ]);

        if (! $this->validarCaptcha($request, $data['captcha'])) {
            return back()->withInput()->withErrors(['captcha' => 'La respuesta no es correcta, inténtelo de nuevo.']);
        }

        $origenWeb = Origen::query()->where('slug', 'pagina-netley')->first();

        unset($data['captcha']);

        Consulta::create([
            ...$data,
            'pais' => 'Bolivia',
            'origen_id' => $origenWeb?->id,
            'estado' => 'nueva',
        ]);

        $request->session()->forget('captcha_respuesta');

        return redirect()->route('home')
            ->with('status', 'Su consulta fue registrada. Le responderemos por correo o WhatsApp en un máximo de 3 días hábiles.');
    }

    /**
     * Captcha matemático simple, validado en el servidor: la respuesta
     * correcta se guarda en la sesión, nunca se manda al navegador. El
     * portal legacy exponía la respuesta en un input hidden del HTML — ver
     * el análisis en docs/, sección "vulnerabilidad a no replicar".
     */
    protected function generarCaptcha(Request $request): array
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);

        $request->session()->put('captcha_respuesta', $a + $b);

        return ['pregunta' => "¿Cuánto es {$a} + {$b}?"];
    }

    protected function validarCaptcha(Request $request, string $respuesta): bool
    {
        $correcta = $request->session()->pull('captcha_respuesta');

        return $correcta !== null && (int) trim($respuesta) === (int) $correcta;
    }
}
