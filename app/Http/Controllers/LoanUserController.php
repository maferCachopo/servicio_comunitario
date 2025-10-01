<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoanUserController extends Controller
{
    /**
     * Muestra una lista de todos los usuarios de préstamo.
     */
    public function index()
    {
        // Buscamos todos los usuarios cuyo rol sea 'loan_user'
        $users = User::where('role', 'loan_user')->latest()->paginate(10);
        return view('loan_users.index', compact('users'));
    }

    /**
     * Muestra el formulario para crear un nuevo usuario de préstamo.
     */
    public function create()
    {
        return view('loan_users.create');
    }

    /**
     * Guarda el nuevo usuario de préstamo en la base de datos.
     */
    public function store(Request $request)
    {
        // 1. Validar los datos del formulario
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // 2. Crear el usuario (el rol por defecto ya es 'loan_user')
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 3. Redirigir a la lista con un mensaje de éxito
        return redirect()->route('loan-users.index')
                         ->with('success', 'Usuario de préstamo creado con éxito.');
    }
}