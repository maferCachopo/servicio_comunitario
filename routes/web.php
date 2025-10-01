<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LoanUserController;
use App\Http\Controllers\InventoryController;

Route::get('/', function () {
     return redirect()->route('login');
});

//carga todas las rutas de autenticacion menos el registro y la de olvide mi contraseña
Auth::routes(['register' => false, 'reset' => false]);

Route::middleware('auth')->group(function () {
    
    // RUTA PARA VER EL PERFIL (Esta ya la arreglaste)
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

    // RUTA PARA EDITAR EL PERFIL (Esta es la que está causando el error ahora)
    // ASEGÚRATE DE QUE ESTA LÍNEA EXISTA Y TENGA ->name('profile.edit')
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');

    // RUTA PARA ACTUALIZAR (Esta no ha cambiado)
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

     Route::resource('loan-users', LoanUserController::class)->middleware('admin');

     // RUTA PARA INVENTARIO (solo admin)
     Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index')->middleware('admin');

});


Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
