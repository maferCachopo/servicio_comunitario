<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Solo crea el usuario si no existe ya para evitar duplicados
        if (!User::where('email', 'admin@example.com')->exists()) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('123456'), 
                'role' => 'admin',
            ]);

            $this->command->info('Usuario administrador creado: admin@example.com / password');
        } else {
            $this->command->info('El usuario administrador ya existe.');
        }
    }
}
