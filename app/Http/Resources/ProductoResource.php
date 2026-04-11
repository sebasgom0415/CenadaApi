<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'nombre'                  => $this->nombre,
            'unidad_comercializacion' => $this->unidad_comercializacion,
            'total_registros'         => $this->precios_count ?? $this->precios->count(),
        ];
    }
}
