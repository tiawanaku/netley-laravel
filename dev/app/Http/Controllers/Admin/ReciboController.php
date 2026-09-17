<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TipoRecibo;
use App\Http\Controllers\Controller;
use App\Models\Proceso;
use App\Models\Recibo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReciboController extends Controller
{
    public function index(Request $request): View
    {
        $recibos = Recibo::query()
            ->with(['proceso.cliente', 'cliente'])
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('fecha', '>=', $request->date('desde')))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('fecha', '<=', $request->date('hasta')))
            ->when($request->filled('buscar'), function ($q) use ($request) {
                $termino = $request->string('buscar');
                $q->where(function ($sub) use ($termino) {
                    $sub->where('numero', 'like', "%{$termino}%")
                        ->orWhere('concepto', 'like', "%{$termino}%");
                });
            })
            ->latest('fecha')->latest('id')
            ->paginate(20)
            ->withQueryString();

        $totalFiltrado = (clone $recibos->getCollection())->sum('monto');

        return view('admin.finanzas.recibos.index', [
            'recibos' => $recibos,
            'totalFiltrado' => $totalFiltrado,
        ]);
    }

    public function create(): View
    {
        return view('admin.finanzas.recibos.create', [
            'procesos' => Proceso::query()->with('cliente')->where('estado', 'activo')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'proceso_id' => ['nullable', 'exists:procesos,id'],
            'fecha' => ['required', 'date'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'concepto' => ['required', 'string', 'max:255'],
        ]);

        $proceso = ! empty($data['proceso_id']) ? Proceso::find($data['proceso_id']) : null;

        Recibo::create([
            ...$data,
            'tipo' => TipoRecibo::Otro,
            'cliente_id' => $proceso?->cliente_id,
            'user_id' => $request->user('web')->id,
        ]);

        return redirect()->route('admin.finanzas.recibos.index')->with('status', 'Recibo registrado correctamente.');
    }

    public function show(Recibo $recibo): View
    {
        $recibo->load(['proceso.cliente', 'cliente', 'planPago', 'user']);

        return view('admin.finanzas.recibos.show', ['recibo' => $recibo]);
    }
}
