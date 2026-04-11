<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = ['nombre', 'unidad_comercializacion'];

    public function precios()
    {
        return $this->hasMany(Precio::class);
    }
}
