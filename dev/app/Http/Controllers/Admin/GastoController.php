<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CategoriaGasto;
use App\Http\Controllers\Controller;
use App\Models\Gasto;
use App\Models\Proceso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GastoController extends Controller
{
    public function index(Request $request): View
    {
        $gastos = Gasto::query()
            ->with('proceso.cliente')
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('fecha', '>=', $request->date('desde')))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('fecha', '<=', $request->date('hasta')))
            ->when($request->filled('categoria'), fn ($q) => $q->where('categoria', $request->string('categoria')))
            ->latest('fecha')->latest('id')
            ->paginate(20)
            ->withQueryString();

        $totalFiltrado = (clone $gastos->getCollection())->sum('monto');

        return view('admin.finanzas.gastos.index', [
            'gastos' => $gastos,
            'totalFiltrado' => $totalFiltrado,
            'categorias' => CategoriaGasto::cases(),
        ]);
    }

    public function create(): View
    {
        return view('admin.finanzas.gastos.create', [
            'procesos' => Proceso::query()->with('cliente')->where('estado', 'activo')->get(),
            'categorias' => CategoriaGasto::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'proceso_id' => ['nullable', 'exists:procesos,id'],
            'fecha' => ['required', 'date'],
            'categoria' => ['required', Rule::enum(CategoriaGasto::class)],
            'descripcion' => ['nullable', 'string'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'comprobante' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
        ], [
            'comprobante.max' => 'El comprobante no puede pesar más de 10 MB.',
            'comprobante.mimes' => 'Formato no permitido para el comprobante. Se aceptan: PDF, JPG o PNG.',
        ]);

        $archivo = $data['comprobante'] ?? null;
        unset($data['comprobante']);

        Gasto::create([
            ...$data,
            'comprobante' => $archivo?->store('gastos', 'public'),
            'comprobante_nombre_original' => $archivo?->getClientOriginalName(),
            'comprobante_mime_type' => $archivo?->getClientMimeType(),
            'comprobante_tamano' => $archivo?->getSize(),
            'user_id' => $request->user('web')->id,
        ]);

        return redirect()->route('admin.finanzas.gastos.index')->with('status', 'Gasto registrado correctamente.');
    }

    public function descargarComprobante(Gasto $gasto): StreamedResponse
    {
        if (! $gasto->comprobante || ! Storage::disk('public')->exists($gasto->comprobante)) {
            abort(404, 'El comprobante ya no está disponible en el servidor.');
        }

        return Storage::disk('public')->download($gasto->comprobante, $gasto->comprobante_nombre_original ?: 'comprobante');
    }

    public function destroy(Gasto $gasto): RedirectResponse
    {
        $gasto->delete();

        return redirect()->route('admin.finanzas.gastos.index')->with('status', 'Gasto eliminado correctamente.');
    }
}
