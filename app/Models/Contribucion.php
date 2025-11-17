<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contribucion extends Model
{
    use HasFactory;

    protected $table = 'contribuciones';

    protected $fillable = [
        'autor_id',
        'obra_id',
        'tipo_contribucion_id'
    ];

    public function autor()
    {
        return $this->belongsTo(Autor::class);
    }

    public function obra()
    {
        return $this->belongsTo(Obra::class);
    }

    public function tipoContribucion()
    {
        return $this->belongsTo(TipoContribucion::class);
    }
}
