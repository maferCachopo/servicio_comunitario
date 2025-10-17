<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    use HasFactory;

    protected $fillable = [
        'partitura_id',
        'estante_id',
        'instrumento',
        'cantidad',
        'cantidad_disponible',
        'estado',
        'notas'
    ];

    public function partitura()
    {
        return $this->belongsTo(Partitura::class);
    }

    public function estante()
    {
        return $this->belongsTo(Estante::class);
    }

    public function prestamos()
    {
        return $this->hasMany(Prestamo::class);
    }
}
