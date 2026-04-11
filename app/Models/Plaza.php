<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plaza extends Model
{
    protected $fillable = ['nombre', 'ubicacion'];

    public function boletines()
    {
        return $this->hasMany(Boletin::class);
    }
}
