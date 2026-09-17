<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gasto;
use App\Models\Recibo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanzasController extends Controller
{
    /**
     * Resumen contable: ingresos (recibos) vs. gastos en un rango de fechas,
     * con balance neto y los últimos movimientos de cada lado.
     */
    public function index(Request $request): View
    {
        $desde = $request->filled('desde')
            ? Carbon::parse($request->string('desde'))->startOfDay()
            : now()->startOfMonth();

        $hasta = $request->filled('hasta')
            ? Carbon::parse($request->string('hasta'))->endOfDay()
            : now()->endOfMonth();

        $totalRecibos = Recibo::query()->whereBetween('fecha', [$desde, $hasta])->sum('monto');
        $totalGastos = Gasto::query()->whereBetween('fecha', [$desde, $hasta])->sum('monto');

        return view('admin.finanzas.index', [
            'desde' => $desde,
            'hasta' => $hasta,
            'totalRecibos' => $totalRecibos,
            'totalGastos' => $totalGastos,
            'balance' => $totalRecibos - $totalGastos,
            'ultimosRecibos' => Recibo::query()->with(['proceso', 'cliente'])->latest('fecha')->latest('id')->limit(8)->get(),
            'ultimosGastos' => Gasto::query()->with('proceso')->latest('fecha')->latest('id')->limit(8)->get(),
        ]);
    }
}
