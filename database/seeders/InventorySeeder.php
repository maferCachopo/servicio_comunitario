<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Autor;
use App\Models\TipoContribucion;
use App\Models\Obra;
use App\Models\Contribucion;
use App\Models\Partitura;
use App\Models\Estante;
use App\Models\Inventario;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Prestamo;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear Autores (5)
        $autores = [
            ['nombre' => 'Ludwig van', 'apellido' => 'Beethoven', 'nacionalidad' => 'Alemán', 'anio_nacimiento' => 1770],
            ['nombre' => 'Wolfgang Amadeus', 'apellido' => 'Mozart', 'nacionalidad' => 'Austríaco', 'anio_nacimiento' => 1756],
            ['nombre' => 'Johann Sebastian', 'apellido' => 'Bach', 'nacionalidad' => 'Alemán', 'anio_nacimiento' => 1685],
            ['nombre' => 'Frédéric', 'apellido' => 'Chopin', 'nacionalidad' => 'Polaco', 'anio_nacimiento' => 1810],
            ['nombre' => 'Antonio', 'apellido' => 'Vivaldi', 'nacionalidad' => 'Italiano', 'anio_nacimiento' => 1678],
        ];

        foreach ($autores as $autor) {
            Autor::create($autor);
        }

        // 2. Crear Tipos de Contribuciones (5)
        $tiposContribucion = [
            ['nombre_contribucion' => 'Compositor', 'descripcion' => 'Persona que compone música'],
            ['nombre_contribucion' => 'Arreglista', 'descripcion' => 'Persona que hace arreglos musicales'],
            ['nombre_contribucion' => 'Transcriptor', 'descripcion' => 'Persona que transcribe música'],
            ['nombre_contribucion' => 'Editor', 'descripcion' => 'Persona que edita partituras'],
            ['nombre_contribucion' => 'Adaptador', 'descripcion' => 'Persona que adapta música para diferentes instrumentos'],
        ];

        foreach ($tiposContribucion as $tipo) {
            TipoContribucion::create($tipo);
        }

        // 3. Crear Obras (5)
        $obras = [
            ['titulo' => 'Sinfonía No. 9 en Re menor', 'anio' => 1824, 'descripcion' => 'Sinfonía final de Beethoven', 'genero' => 'Sinfonía', 'duracion_minutos' => 70],
            ['titulo' => 'Concierto para Piano No. 21', 'anio' => 1785, 'descripcion' => 'Concierto para piano y orquesta', 'genero' => 'Concierto', 'duracion_minutos' => 32],
            ['titulo' => 'El Arte de la Fuga', 'anio' => 1750, 'descripcion' => 'Obra maestra contrapuntística', 'genero' => 'Fuga', 'duracion_minutos' => 90],
            ['titulo' => 'Nocturno en Do sostenido menor', 'anio' => 1830, 'descripcion' => 'Pieza para piano solo', 'genero' => 'Nocturno', 'duracion_minutos' => 5],
            ['titulo' => 'Las Cuatro Estaciones', 'anio' => 1725, 'descripcion' => 'Conciertos para violín y orquesta', 'genero' => 'Concierto', 'duracion_minutos' => 40],
        ];

        foreach ($obras as $obra) {
            Obra::create($obra);
        }

        // 4. Crear Contribuciones (relaciones entre autores y obras)
        $contribuciones = [
            ['autor_id' => 1, 'obra_id' => 1, 'tipo_contribucion_id' => 1], // Beethoven - Sinfonía 9 - Compositor
            ['autor_id' => 2, 'obra_id' => 2, 'tipo_contribucion_id' => 1], // Mozart - Concierto 21 - Compositor
            ['autor_id' => 3, 'obra_id' => 3, 'tipo_contribucion_id' => 1], // Bach - Arte de la Fuga - Compositor
            ['autor_id' => 4, 'obra_id' => 4, 'tipo_contribucion_id' => 1], // Chopin - Nocturno - Compositor
            ['autor_id' => 5, 'obra_id' => 5, 'tipo_contribucion_id' => 1], // Vivaldi - 4 Estaciones - Compositor
        ];

        foreach ($contribuciones as $contribucion) {
            Contribucion::create($contribucion);
        }

        // 5. Crear Partituras (5)
        $partituras = [
            ['obra_id' => 1, 'tipo_partitura' => 'Partitura completa', 'formato' => 'PDF', 'numero_paginas' => 150, 'idioma' => 'Alemán'],
            ['obra_id' => 2, 'tipo_partitura' => 'Partitura de piano', 'formato' => 'PDF', 'numero_paginas' => 45, 'idioma' => 'Italiano'],
            ['obra_id' => 3, 'tipo_partitura' => 'Partitura completa', 'formato' => 'PDF', 'numero_paginas' => 200, 'idioma' => 'Latín'],
            ['obra_id' => 4, 'tipo_partitura' => 'Partitura de piano', 'formato' => 'PDF', 'numero_paginas' => 8, 'idioma' => 'Francés'],
            ['obra_id' => 5, 'tipo_partitura' => 'Partitura de violín', 'formato' => 'PDF', 'numero_paginas' => 60, 'idioma' => 'Italiano'],
        ];

        foreach ($partituras as $partitura) {
            Partitura::create($partitura);
        }

        // 6. Crear Estantes (5)
        $estantes = [
            ['codigo_estante' => 'A-01', 'gaveta' => 'G-01', 'seccion' => 'Clásica', 'descripcion_ubicacion' => 'Estante A, Gaveta 1, Sección Clásica'],
            ['codigo_estante' => 'A-02', 'gaveta' => 'G-02', 'seccion' => 'Romántica', 'descripcion_ubicacion' => 'Estante A, Gaveta 2, Sección Romántica'],
            ['codigo_estante' => 'B-01', 'gaveta' => 'G-03', 'seccion' => 'Barroca', 'descripcion_ubicacion' => 'Estante B, Gaveta 3, Sección Barroca'],
            ['codigo_estante' => 'B-02', 'gaveta' => 'G-04', 'seccion' => 'Conciertos', 'descripcion_ubicacion' => 'Estante B, Gaveta 4, Sección Conciertos'],
            ['codigo_estante' => 'C-01', 'gaveta' => 'G-05', 'seccion' => 'Piano', 'descripcion_ubicacion' => 'Estante C, Gaveta 5, Sección Piano'],
        ];

        foreach ($estantes as $estante) {
            Estante::create($estante);
        }

        // 7. Crear Inventarios (5)
        $inventarios = [
            ['partitura_id' => 1, 'estante_id' => 1, 'instrumento' => 'Orquesta', 'cantidad' => 5, 'cantidad_disponible' => 3, 'estado' => 'disponible', 'notas' => 'Partituras en buen estado'],
            ['partitura_id' => 2, 'estante_id' => 4, 'instrumento' => 'Piano', 'cantidad' => 3, 'cantidad_disponible' => 2, 'estado' => 'disponible', 'notas' => 'Edición especial'],
            ['partitura_id' => 3, 'estante_id' => 3, 'instrumento' => 'Órgano', 'cantidad' => 2, 'cantidad_disponible' => 1, 'estado' => 'disponible', 'notas' => 'Edición crítica'],
            ['partitura_id' => 4, 'estante_id' => 5, 'instrumento' => 'Piano', 'cantidad' => 8, 'cantidad_disponible' => 5, 'estado' => 'disponible', 'notas' => 'Partituras populares'],
            ['partitura_id' => 5, 'estante_id' => 2, 'instrumento' => 'Violín', 'cantidad' => 4, 'cantidad_disponible' => 2, 'estado' => 'disponible', 'notas' => 'Incluye partes separadas'],
        ];

        foreach ($inventarios as $inventario) {
            Inventario::create($inventario);
        }

        // 8. Crear Usuarios de Préstamo (5) - solo si no existen
        $usuarios = [
            ['name' => 'María González', 'email' => 'maria.gonzalez@email.com', 'password' => Hash::make('password'), 'role' => 'loan_user'],
            ['name' => 'Carlos Rodríguez', 'email' => 'carlos.rodriguez@email.com', 'password' => Hash::make('password'), 'role' => 'loan_user'],
            ['name' => 'Ana Martínez', 'email' => 'ana.martinez@email.com', 'password' => Hash::make('password'), 'role' => 'loan_user'],
            ['name' => 'Luis Pérez', 'email' => 'luis.perez@email.com', 'password' => Hash::make('password'), 'role' => 'loan_user'],
            ['name' => 'Elena Sánchez', 'email' => 'elena.sanchez@email.com', 'password' => Hash::make('password'), 'role' => 'loan_user'],
        ];

        foreach ($usuarios as $usuario) {
            if (!User::where('email', $usuario['email'])->exists()) {
                User::create($usuario);
            }
        }

        // 9. Crear Préstamos (5)
        $borrowerIds = User::where('role', 'loan_user')->pluck('id')->toArray();
        $prestamos = [
            ['inventario_id' => 1, 'user_id' => $borrowerIds[0], 'fecha_prestamo' => '2025-09-15 10:00:00', 'fecha_devolucion' => '2025-09-22 10:00:00', 'estado' => 'devuelto', 'descripcion' => 'Préstamo para ensayo general'],
            ['inventario_id' => 2, 'user_id' => $borrowerIds[1], 'fecha_prestamo' => '2025-09-20 14:30:00', 'fecha_devolucion' => null, 'estado' => 'activo', 'descripcion' => 'Préstamo para clase de piano'],
            ['inventario_id' => 3, 'user_id' => $borrowerIds[2], 'fecha_prestamo' => '2025-09-25 09:15:00', 'fecha_devolucion' => '2025-10-02 09:15:00', 'estado' => 'activo', 'descripcion' => 'Préstamo para investigación'],
            ['inventario_id' => 4, 'user_id' => $borrowerIds[3], 'fecha_prestamo' => '2025-09-28 16:45:00', 'fecha_devolucion' => null, 'estado' => 'activo', 'descripcion' => 'Préstamo para práctica personal'],
            ['inventario_id' => 5, 'user_id' => $borrowerIds[4], 'fecha_prestamo' => '2025-09-30 11:20:00', 'fecha_devolucion' => '2025-10-07 11:20:00', 'estado' => 'activo', 'descripcion' => 'Préstamo para concierto estudiantil'],
        ];

        foreach ($prestamos as $prestamo) {
            Prestamo::create($prestamo);
        }
    }
}
