<?php

namespace App\Http\Controllers;

use App\Models\Precio;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::withCount('precios')->orderBy('nombre');

        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', '%' . $request->buscar . '%');
        }

        $productos = $query->paginate(20)->withQueryString();

        return view('productos.index', compact('productos'));
    }

    public function show(Producto $producto)
    {
        $precios = Precio::with('boletin.plaza')
            ->where('producto_id', $producto->id)
            ->join('boletines', 'precios.boletin_id', '=', 'boletines.id')
            ->orderBy('boletines.fecha_plaza')
            ->select('precios.*')
            ->get();

        $chartData = [
            'labels'   => $precios->map(fn($p) => $p->boletin->fecha_plaza->format('d/m/Y'))->toArray(),
            'minimo'   => $precios->pluck('precio_minimo')->toArray(),
            'maximo'   => $precios->pluck('precio_maximo')->toArray(),
            'promedio' => $precios->pluck('promedio')->toArray(),
        ];

        return view('productos.show', compact('producto', 'precios', 'chartData'));
    }
}
