<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MiCuentaController extends Controller
{
    public function index()
    {
        $user  = auth()->user();
        $logs  = \App\Models\ApiLog::where('user_id', $user->id)
            ->latest('created_at')
            ->paginate(15);

        $totalConsultas = \App\Models\ApiLog::where('user_id', $user->id)->count();
        $hoy = \App\Models\ApiLog::where('user_id', $user->id)
            ->whereDate('created_at', today())->count();

        return view('mi-cuenta.index', compact('user', 'logs', 'totalConsultas', 'hoy'));
    }

    public function regenerarToken()
    {
        $plainToken = \Illuminate\Support\Str::random(60);
        auth()->user()->update(['api_token' => hash('sha256', $plainToken)]);
        return back()->with('token', $plainToken);
    }
}
