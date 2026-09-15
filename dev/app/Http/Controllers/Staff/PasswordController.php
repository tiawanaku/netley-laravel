<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function edit(): View
    {
        return view('staff.auth.password');
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $personal = $request->user('personal');
        $personal->password = $data['password'];
        $personal->must_change_password = false;
        $personal->save();

        return redirect()->route('staff.dashboard')->with('status', 'Contraseña actualizada correctamente.');
    }
}
