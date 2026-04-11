<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrecioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'producto'              => $this->producto->nombre,
            'unidad_comercializacion' => $this->producto->unidad_comercializacion,
            'precio_minimo'         => (float) $this->precio_minimo,
            'precio_maximo'         => (float) $this->precio_maximo,
            'moda'                  => (float) $this->moda,
            'promedio'              => (float) $this->promedio,
        ];
    }
}
