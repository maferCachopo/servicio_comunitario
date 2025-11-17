<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prestamo;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $pendingLoansCount = 0;
        
        // Check if the authenticated user is an admin
        if (auth()->check() && auth()->user()->role === 'admin') {
            $pendingLoansCount = Prestamo::where('estado', 'Pendiente')->count();
        }
        
        return view('home')->with('pendingLoansCount', $pendingLoansCount);
    }
}
