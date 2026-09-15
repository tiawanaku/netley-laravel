<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlanPago;
use Illuminate\Http\RedirectResponse;

class PlanPagoController extends Controller
{
    public function confirmar(PlanPago $planPago): RedirectResponse
    {
        if ($planPago->estado->value !== 'pagado') {
            $planPago->confirmarPago();
        }

        return redirect()->route('admin.procesos.show', $planPago->finanza->proceso_id)
            ->with('status', 'Pago confirmado correctamente.');
    }
}
