<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Precio extends Model
{
    protected $fillable = [
        'boletin_id', 'producto_id',
        'precio_minimo', 'precio_maximo', 'moda', 'promedio'
    ];

    public function boletin()
    {
        return $this->belongsTo(Boletin::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
