<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partitura extends Model
{
    use HasFactory;

    protected $fillable = [
        'obra_id',
        'tipo_partitura',
        'formato',
        'numero_paginas',
        'idioma'
    ];

    public function obra()
    {
        return $this->belongsTo(Obra::class);
    }

    public function inventarios()
    {
        return $this->hasMany(Inventario::class);
    }
}
