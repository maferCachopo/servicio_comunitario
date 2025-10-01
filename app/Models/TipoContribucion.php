<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoContribucion extends Model
{
    use HasFactory;

    protected $table = 'tipo_contribuciones';

    protected $fillable = [
        'nombre_contribucion',
        'descripcion'
    ];

    public function contribuciones()
    {
        return $this->hasMany(Contribucion::class);
    }
}
