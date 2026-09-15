<?php

namespace App\Http\Controllers\Staff\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('staff.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'usuario' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('personal')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['usuario' => 'Credenciales incorrectas.'])->onlyInput('usuario');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('staff.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('personal')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('staff.login');
    }
}
