<?php

namespace App\Http\Controllers;

use App\Models\Boletin;
use App\Models\Precio;
use App\Models\Producto;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalBoletines = Boletin::count();
        $totalProductos = Producto::count();

        // Todas las fechas disponibles para el selector
        $fechasDisponibles = Boletin::orderByDesc('fecha_plaza')
            ->pluck('fecha_plaza', 'id');

        $ultimosPrecios = [];
        $productosChart = collect();
        $chartData      = ['labels' => [], 'data' => []];
        $unidadesChart  = ['labels' => [], 'data' => []];
        $boletinActivo  = null;

        if ($totalBoletines > 0) {
            // Si viene un boletin_id en el request lo usamos, sino el más reciente
            $boletinActivo = $request->filled('boletin_id')
                ? Boletin::findOrFail($request->boletin_id)
                : Boletin::latest('fecha_plaza')->first();

            $ultimosPrecios = Precio::with('producto')
                ->where('boletin_id', $boletinActivo->id)
                ->get()
                ->sortBy('producto.nombre');

            $productosChart = Producto::orderBy('nombre')->get();

            if ($productosChart->isNotEmpty()) {
                $chartData = $this->getChartData($productosChart->first()->id);
            }

            $unidades = Producto::selectRaw('unidad_comercializacion, count(*) as total')
                ->groupBy('unidad_comercializacion')
                ->orderByDesc('total')
                ->get();

            $unidadesChart = [
                'labels' => $unidades->pluck('unidad_comercializacion')->toArray(),
                'data'   => $unidades->pluck('total')->toArray(),
            ];
        }

        $ultimaFecha = $boletinActivo?->fecha_plaza->format('d/m/Y');

        return view('dashboard.index', compact(
            'totalBoletines', 'totalProductos', 'ultimaFecha',
            'ultimosPrecios', 'productosChart', 'chartData',
            'unidadesChart', 'fechasDisponibles', 'boletinActivo'
        ));
    }

    public function chart(Request $request)
    {
        $data = $this->getChartData($request->producto_id);
        return response()->json($data);
    }

    private function getChartData(int $productoId): array
    {
        $precios = Precio::with('boletin')
            ->where('producto_id', $productoId)
            ->join('boletines', 'precios.boletin_id', '=', 'boletines.id')
            ->orderBy('boletines.fecha_plaza')
            ->select('precios.*')
            ->get();

        return [
            'labels' => $precios->map(fn($p) => $p->boletin->fecha_plaza->format('d/m/Y'))->toArray(),
            'data'   => $precios->pluck('promedio')->toArray(),
        ];
    }
}
