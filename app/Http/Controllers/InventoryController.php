<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Muestra la página de bienvenida del inventario.
     */
    public function index()
    {
        return view('inventory.index');
    }
}