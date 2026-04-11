<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RegistroController extends Controller
{
    public function show()
    {
        if (auth()->check()) {
            return redirect()->route('mi-cuenta.index');
        }
        return view('registro');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ], [
            'email.unique'      => 'Este correo ya está registrado.',
            'password.min'      => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $plainToken = \Illuminate\Support\Str::random(60);

        $user = \App\Models\User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => \Illuminate\Support\Facades\Hash::make($request->password),
            'role'      => 'api',
            'is_active' => true,
            'api_token' => hash('sha256', $plainToken),
        ]);

        auth()->login($user);

        return redirect()->route('mi-cuenta.index')->with('token', $plainToken);
    }
}
