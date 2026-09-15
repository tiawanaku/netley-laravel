<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $cliente = $request->user('clientes');

        return view('portal.dashboard', [
            'cliente' => $cliente,
            'procesos' => $cliente->procesos()->with('finanza', 'abogado', 'etapas')->get(),
        ]);
    }
}
