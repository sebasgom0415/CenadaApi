<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiLogsController extends Controller
{
    public function index()
    {
        $logs = \App\Models\ApiLog::with('user')
            ->latest('created_at')
            ->paginate(30);

        $totalHoy   = \App\Models\ApiLog::whereDate('created_at', today())->count();
        $totalTotal = \App\Models\ApiLog::count();

        $topEndpoints = \App\Models\ApiLog::selectRaw('endpoint, count(*) as total')
            ->groupBy('endpoint')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $topUsuarios = \App\Models\ApiLog::selectRaw('user_id, count(*) as total')
            ->with('user')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $totalUsuarios = \App\Models\ApiLog::distinct('user_id')->count('user_id');

        return view('admin.api-logs', compact(
            'logs', 'totalHoy', 'totalTotal', 'topEndpoints', 'topUsuarios', 'totalUsuarios'
        ));
    }
}
