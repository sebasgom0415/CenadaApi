<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductoApiController extends Controller
{
    // GET /api/productos
    // Catálogo completo de productos
    public function index()
    {
        $productos = \App\Models\Producto::withCount('precios')->orderBy('nombre')->get();

        return response()->json([
            'success' => true,
            'total'   => $productos->count(),
            'data'    => \App\Http\Resources\ProductoResource::collection($productos),
        ]);
    }

    // GET /api/productos/{id}/historial
    // Historial de precios de un producto por todas las fechas
    public function historial(\App\Models\Producto $producto)
    {
        $precios = \App\Models\Precio::with('boletin')
            ->where('producto_id', $producto->id)
            ->join('boletines', 'precios.boletin_id', '=', 'boletines.id')
            ->orderBy('boletines.fecha_plaza')
            ->select('precios.*')
            ->get()
            ->map(fn($p) => [
                'fecha_plaza'   => $p->boletin->fecha_plaza->format('Y-m-d'),
                'precio_minimo' => (float) $p->precio_minimo,
                'precio_maximo' => (float) $p->precio_maximo,
                'moda'          => (float) $p->moda,
                'promedio'      => (float) $p->promedio,
            ]);

        return response()->json([
            'success'  => true,
            'producto' => $producto->nombre,
            'unidad'   => $producto->unidad_comercializacion,
            'total'    => $precios->count(),
            'data'     => $precios,
        ]);
    }
}
