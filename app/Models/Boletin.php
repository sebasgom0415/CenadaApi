<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Boletin extends Model
{
    protected $table = 'boletines';

    protected $fillable = ['plaza_id', 'fecha_plaza', 'tipo_cambio_usd', 'archivo_pdf'];

    protected $casts = ['fecha_plaza' => 'date'];

    public function plaza()
    {
        return $this->belongsTo(Plaza::class);
    }

    public function precios()
    {
        return $this->hasMany(Precio::class);
    }
}
