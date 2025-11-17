<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage; 

class ProfileController extends Controller
{
    /**
     * Muestra el perfil del usuario.
     */
    public function show(){
        return view('profile.show', [
            'user' => Auth::user()
        ]);
    }
    /**
     * Muestra el formulario para editar el perfil del usuario autenticado.
     */
    public function edit()
    {
        return view('profile.edit', [
            'user' => Auth::user()
        ]);
    }

    /**
     * Actualiza el perfil del usuario autenticado.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // La contraseña es opcional, solo se valida si se escribe algo.
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'photo' => ['nullable', 'image', 'max:2048'], // max 2MB
        ]);

        // Actualizar nombre
        $user->name = $request->name;

        // Actualizar contraseña si se proporcionó una nueva
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Subir y actualizar foto de perfil si se proporcionó una nueva
        if ($request->hasFile('photo')) {
            // Borrar la foto anterior si existe
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            // Guardar la nueva foto y obtener su ruta
            $path = $request->file('photo')->store('profile-photos', 'public');
            $user->profile_photo_path = $path;
        }

        $user->save();

        return back()->with('status', '¡Perfil actualizado con éxito!');
    }
}
