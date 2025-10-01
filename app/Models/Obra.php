<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Obra extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'anio',
        'descripcion',
        'genero',
        'duracion_minutos'
    ];

    public function partituras()
    {
        return $this->hasMany(Partitura::class);
    }

    public function contribuciones()
    {
        return $this->hasMany(Contribucion::class);
    }

    public function autores()
    {
        return $this->belongsToMany(Autor::class, 'contribuciones')
                    ->withPivot('tipo_contribucion_id')
                    ->withTimestamps();
    }
}
