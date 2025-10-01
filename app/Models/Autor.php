<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Autor extends Model
{
    use HasFactory;

    protected $table = 'autores';

    protected $fillable = [
        'nombre',
        'apellido',
        'nacionalidad',
        'anio_nacimiento'
    ];

    public function contribuciones()
    {
        return $this->hasMany(Contribucion::class);
    }

    public function obras()
    {
        return $this->belongsToMany(Obra::class, 'contribuciones')
                    ->withPivot('tipo_contribucion_id')
                    ->withTimestamps();
    }
}
