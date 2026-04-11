<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PublicoController extends Controller
{
    public function index(Request $request)
    {
        $fechasDisponibles = \App\Models\Boletin::orderByDesc('fecha_plaza')
            ->pluck('fecha_plaza', 'id');

        $totalBoletines = $fechasDisponibles->count();
        $totalProductos = \App\Models\Producto::count();

        $boletinActivo = null;
        $precios       = collect();
        $unidades      = collect();
        $productosLista = collect();
        $chartData     = ['labels' => [], 'data' => []];
        $unidadesChart = ['labels' => [], 'data' => []];

        if ($totalBoletines > 0) {
            $boletinActivo = $request->filled('boletin_id')
                ? \App\Models\Boletin::findOrFail($request->boletin_id)
                : \App\Models\Boletin::latest('fecha_plaza')->first();

            $query = \App\Models\Precio::with('producto')
                ->where('boletin_id', $boletinActivo->id);

            if ($request->filled('unidad')) {
                $query->whereHas('producto', fn($q) => $q->where('unidad_comercializacion', $request->unidad));
            }

            if ($request->filled('buscar')) {
                $query->whereHas('producto', fn($q) => $q->where('nombre', 'like', '%' . $request->buscar . '%'));
            }

            $precios = $query->get()->sortBy('producto.nombre');

            $unidades = \App\Models\Producto::whereHas('precios', fn($q) => $q->where('boletin_id', $boletinActivo->id))
                ->distinct()->orderBy('unidad_comercializacion')
                ->pluck('unidad_comercializacion');

            // Chart evolución — primer producto disponible o el buscado
            $productosLista = \App\Models\Producto::orderBy('nombre')->get();

            $productoChartId = $request->filled('producto_chart')
                ? $request->producto_chart
                : $productosLista->first()?->id;

            if ($productoChartId) {
                $chartData = $this->getChartData($productoChartId);
            }

            $unidadesData = \App\Models\Producto::selectRaw('unidad_comercializacion, count(*) as total')
                ->groupBy('unidad_comercializacion')->orderByDesc('total')->get();

            $unidadesChart = [
                'labels' => $unidadesData->pluck('unidad_comercializacion')->toArray(),
                'data'   => $unidadesData->pluck('total')->toArray(),
            ];
        }

        return view('publico.index', compact(
            'fechasDisponibles', 'totalBoletines', 'totalProductos',
            'boletinActivo', 'precios', 'unidades',
            'productosLista', 'chartData', 'unidadesChart'
        ));
    }

    public function chart(Request $request)
    {
        return response()->json($this->getChartData($request->producto_id));
    }

    private function getChartData(int $productoId): array
    {
        $precios = \App\Models\Precio::with('boletin')
            ->where('producto_id', $productoId)
            ->join('boletines', 'precios.boletin_id', '=', 'boletines.id')
            ->orderBy('boletines.fecha_plaza')
            ->select('precios.*')
            ->get();

        return [
            'labels'   => $precios->map(fn($p) => $p->boletin->fecha_plaza->format('d/m/Y'))->toArray(),
            'promedio' => $precios->pluck('promedio')->toArray(),
            'minimo'   => $precios->pluck('precio_minimo')->toArray(),
            'maximo'   => $precios->pluck('precio_maximo')->toArray(),
        ];
    }
}
