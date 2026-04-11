<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiUsuariosController extends Controller
{
    public function index()
    {
        $usuarios = \App\Models\User::where('role', 'api')
            ->withCount('apiLogs')
            ->with(['apiLogs' => fn($q) => $q->latest('created_at')->limit(1)])
            ->latest()
            ->paginate(20);

        $total          = \App\Models\User::where('role', 'api')->count();
        $activos        = \App\Models\User::where('role', 'api')->where('is_active', true)->count();
        $inactivos      = $total - $activos;
        $totalConsultas = \App\Models\ApiLog::count();

        // Attach lastLog accessor
        $usuarios->each(function ($u) {
            $u->lastLog = $u->apiLogs->first();
        });

        return view('admin.api-usuarios.index', compact('usuarios', 'total', 'activos', 'inactivos', 'totalConsultas'));
    }

    public function show(\App\Models\User $user)
    {
        $usuario = $user;

        $logs = \App\Models\ApiLog::where('user_id', $user->id)
            ->latest('created_at')
            ->paginate(20);

        $totalConsultas = \App\Models\ApiLog::where('user_id', $user->id)->count();
        $hoy = \App\Models\ApiLog::where('user_id', $user->id)
            ->whereDate('created_at', today())->count();
        $semana = \App\Models\ApiLog::where('user_id', $user->id)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();

        $topEndpoints = \App\Models\ApiLog::where('user_id', $user->id)
            ->selectRaw('endpoint, count(*) as total')
            ->groupBy('endpoint')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('admin.api-usuarios.show', compact('usuario', 'logs', 'totalConsultas', 'hoy', 'semana', 'topEndpoints'));
    }

    public function toggleActivo(\App\Models\User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        $estado = $user->is_active ? 'activado' : 'desactivado';
        return back()->with('success', "Usuario {$estado} correctamente.");
    }

    public function destroy(\App\Models\User $user)
    {
        $user->delete();
        return response()->json(['ok' => true]);
    }
}
