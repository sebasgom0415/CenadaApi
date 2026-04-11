<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BoletinApiController extends Controller
{
    // GET /api/boletines
    // Lista todas las fechas disponibles
    public function index()
    {
        $boletines = \App\Models\Boletin::with('plaza')
            ->withCount('precios')
            ->orderByDesc('fecha_plaza')
            ->get()
            ->map(fn($b) => [
                'id'              => $b->id,
                'fecha_plaza'     => $b->fecha_plaza->format('Y-m-d'),
                'plaza'           => $b->plaza->nombre,
                'total_productos' => $b->precios_count,
            ]);

        return response()->json([
            'success' => true,
            'total'   => $boletines->count(),
            'data'    => $boletines,
        ]);
    }

    // GET /api/boletines/latest
    // Retorna el boletín más reciente con todos sus precios
    public function latest()
    {
        $boletin = \App\Models\Boletin::with(['plaza', 'precios.producto'])
            ->latest('fecha_plaza')
            ->first();

        if (!$boletin) {
            return response()->json(['success' => false, 'message' => 'No hay boletines disponibles.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => new \App\Http\Resources\BoletinResource($boletin),
        ]);
    }

    // GET /api/boletines/{fecha}   formato: YYYY-MM-DD
    // Retorna el boletín de esa fecha con todos sus precios
    public function show(string $fecha)
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return response()->json([
                'success' => false,
                'message' => 'Formato de fecha inválido. Use YYYY-MM-DD (ej: 2026-04-10).',
            ], 422);
        }

        $boletin = \App\Models\Boletin::with(['plaza', 'precios.producto'])
            ->whereDate('fecha_plaza', $fecha)
            ->first();

        if (!$boletin) {
            return response()->json([
                'success' => false,
                'message' => "No existe boletín para la fecha $fecha.",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => new \App\Http\Resources\BoletinResource($boletin),
        ]);
    }

    // GET /api/boletines/{fecha}/producto/{nombre}
    // Precio de un producto específico en una fecha
    public function producto(string $fecha, string $nombre)
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return response()->json(['success' => false, 'message' => 'Formato de fecha inválido. Use YYYY-MM-DD.'], 422);
        }

        $boletin = \App\Models\Boletin::whereDate('fecha_plaza', $fecha)->first();

        if (!$boletin) {
            return response()->json(['success' => false, 'message' => "No existe boletín para la fecha $fecha."], 404);
        }

        $precio = \App\Models\Precio::with('producto')
            ->where('boletin_id', $boletin->id)
            ->whereHas('producto', fn($q) => $q->where('nombre', 'like', '%' . $nombre . '%'))
            ->get();

        if ($precio->isEmpty()) {
            return response()->json(['success' => false, 'message' => "Producto '$nombre' no encontrado en el boletín de $fecha."], 404);
        }

        return response()->json([
            'success'     => true,
            'fecha_plaza' => $fecha,
            'data'        => \App\Http\Resources\PrecioResource::collection($precio),
        ]);
    }
}
