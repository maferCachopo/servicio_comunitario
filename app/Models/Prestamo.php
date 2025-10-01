<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventario_id',
        'user_id',
        'fecha_prestamo',
        'fecha_devolucion',
        'estado',
        'descripcion'
    ];

    protected $dates = [
        'fecha_prestamo',
        'fecha_devolucion'
    ];

    public function inventario()
    {
        return $this->belongsTo(Inventario::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
