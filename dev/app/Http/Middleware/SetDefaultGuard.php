<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hace que Auth::user()/Auth::check() (guard por defecto) resuelvan sobre el
 * guard del panel actual — lo necesitan las vistas de AdminLTE (navbar,
 * user-menu) que no reciben el guard explícitamente.
 */
class SetDefaultGuard
{
    public function handle(Request $request, Closure $next, string $guard): Response
    {
        Auth::shouldUse($guard);

        return $next($request);
    }
}
