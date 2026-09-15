<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Cada uno de los 3 paneles (admin/staff/portal) tiene su propio login
        // nombrado. Sin esto, un visitante sin sesión que cae en una ruta
        // protegida de /staff o /portal sería redirigido al login de Admin
        // (o a una ruta "login" inexistente, error 500 documentado en el
        // proyecto original — ver ADR de docs/Decisiones Arquitectónicas.md).
        Authenticate::redirectUsing(fn (Request $request) => $this->loginRouteFor($request));
        RedirectIfAuthenticated::redirectUsing(fn (Request $request) => $this->dashboardRouteFor($request));
    }

    protected function loginRouteFor(Request $request): string
    {
        return match (true) {
            $request->is('staff*') => route('staff.login'),
            $request->is('portal*') => route('portal.login'),
            default => route('admin.login'),
        };
    }

    protected function dashboardRouteFor(Request $request): string
    {
        return match (true) {
            $request->is('staff*') => route('staff.dashboard'),
            $request->is('portal*') => route('portal.dashboard'),
            default => route('admin.dashboard'),
        };
    }
}
