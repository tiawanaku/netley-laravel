<?php

namespace App\Http\Middleware;

use App\Enums\EstadoPersonal;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Equivalente a Personal::canAccessPanel() del panel Filament original:
 * un Personal deshabilitado no puede navegar el panel Staff aunque su
 * sesión siga vigente.
 */
class EnsurePersonalActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $personal = $request->user('personal');

        if ($personal && $personal->estado !== EstadoPersonal::Habilitado) {
            auth('personal')->logout();
            $request->session()->invalidate();

            return redirect()->route('staff.login')
                ->withErrors(['usuario' => 'Tu cuenta no está activa. Contacta al administrador.']);
        }

        return $next($request);
    }
}
