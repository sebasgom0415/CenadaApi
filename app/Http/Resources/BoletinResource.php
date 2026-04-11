<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BoletinResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'fecha_plaza'     => $this->fecha_plaza->format('Y-m-d'),
            'plaza'           => $this->plaza->nombre,
            'ubicacion'       => $this->plaza->ubicacion,
            'total_productos' => $this->precios->count(),
            'precios'         => PrecioResource::collection($this->precios),
        ];
    }
}
