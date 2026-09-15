<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePersonalPasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $personal = $request->user('personal');

        if ($personal && $personal->must_change_password && ! $request->routeIs('staff.password.*')) {
            return redirect()->route('staff.password.edit');
        }

        return $next($request);
    }
}
