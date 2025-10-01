<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estante extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo_estante',
        'gaveta',
        'seccion',
        'descripcion_ubicacion'
    ];

    public function inventarios()
    {
        return $this->hasMany(Inventario::class);
    }
}
