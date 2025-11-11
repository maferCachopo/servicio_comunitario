<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LoanUserController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\HomeController;

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

     Route::resource('loan_users', LoanUserController::class)->middleware('admin');

     // RUTA PARA INVENTARIO (solo admin)
     Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index')->middleware('admin');
     Route::get('/inventory/prestamos-data', [InventoryController::class, 'getPrestamosData'])->name('inventory.prestamos.data')->middleware('admin');
     Route::get('/inventory/partituras-data', [InventoryController::class, 'getPartiturasData'])->name('inventory.partituras.data')->middleware('admin');
     Route::get('/home', [HomeController::class, 'index'])->name('home')->middleware('admin'); 
    // RUTA PARA ACTUALIZAR INVENTARIO (PUT)
     Route::put('/inventory/{partitura_id}/{estante_id_original}', [InventoryController::class, 'update'])->name('inventory.update')->middleware('admin');

     // API Routes for Loan System
     Route::prefix('api')->group(function () {
         // Loan user endpoints
         Route::get('/partituras-disponibles', [LoanUserController::class, 'partiturasDisponibles'])->name('api.partituras.disponibles');
         Route::post('/solicitar-prestamo', [LoanUserController::class, 'solicitarPrestamo'])->name('api.solicitar.prestamo');
         Route::get('/mis-prestamos', [LoanUserController::class, 'misPrestamos'])->name('api.mis.prestamos');
         
         // Admin endpoints
         Route::get('/prestamos-pendientes', [InventoryController::class, 'prestamosPendientes'])->name('api.prestamos.pendientes')->middleware('admin');
         Route::post('/procesar-prestamo/{id}', [InventoryController::class, 'procesarPrestamo'])->name('api.procesar.prestamo')->middleware('admin');
     });

     // RUTA PARA PRÉSTAMO DE PARTITURAS - Solo para usuarios loan_user
     Route::get('/loan-request', function() {
         return view('loan_request.index');
     })->name('loan.request')->middleware('auth');

});

// DEBUG: Test route for loan section
Route::get('/debug-loan', function() {
    return view('debug.loan_test');
})->name('debug.loan');


