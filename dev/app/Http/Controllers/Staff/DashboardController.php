<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $personal = $request->user('personal');

        return view('staff.dashboard', [
            'personal' => $personal,
            'agendaHoy' => $personal->agendas()->deHoy()->get(),
            'misProcesos' => $personal->esAdministrador()
                ? \App\Models\Proceso::where('estado', 'activo')->count()
                : $personal->procesos()->where('estado', 'activo')->count(),
        ]);
    }
}
