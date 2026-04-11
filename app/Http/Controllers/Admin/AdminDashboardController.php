<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalBoletines  = \App\Models\Boletin::count();
        $totalProductos  = \App\Models\Producto::count();
        $ultimoBoletin   = \App\Models\Boletin::latest('fecha_plaza')->first();
        $ultimaFecha     = $ultimoBoletin?->fecha_plaza->format('d/m/Y');

        $ultimosBoletines = \App\Models\Boletin::with('plaza')
            ->withCount('precios')
            ->latest('fecha_plaza')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact(
            'totalBoletines', 'totalProductos', 'ultimaFecha', 'ultimosBoletines'
        ));
    }
}
