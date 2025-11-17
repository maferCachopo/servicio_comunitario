<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Partitura;
use App\Models\Prestamo;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; 

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
   // En LoanUserController.php del CLIENTE

    public function store(Request $request)
    {
        // 1. Validar los datos del formulario (esto no cambia)
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'], // Valida unicidad localmente para evitar peticiones innecesarias
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // 2. Enviar los datos al SERVIDOR
        $response = Http::post('http://localhost:8050/api/v1/registrar-usuario', [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'], // Enviamos la contraseña en texto plano, el servidor la encriptará
        ]);

        // 3. Manejar la respuesta del SERVIDOR
        if ($response->failed()) {
            // Si el servidor devolvió un error (ej. 500, o 422 por un email que ya existe allá)
            return back()->with('error', 'No se pudo crear el usuario en el servidor. Intente de nuevo.')->withInput();
        }
        
        // 4. (Opcional pero recomendado) Crear el usuario también en la base de datos local.
        // Esto es útil para que el usuario pueda iniciar sesión en el cliente inmediatamente.
        // Asegúrate de que los datos coincidan.
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'loan_user' // Asignar el rol correcto
        ]);

        // 5. Redirigir con mensaje de éxito
        return redirect()->route('loan_users.index')
                        ->with('success', 'Usuario de préstamo creado con éxito en ambos sistemas.');
    }

    /**
     * API: Get available partituras with instruments and stock
     */
    public function partiturasDisponibles()
    {
        try {
            // Validate request
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $partituras = Partitura::with(['obra.contribuciones.autor', 'inventarios'])
                ->whereHas('inventarios', function($query) {
                    $query->where('cantidad_disponible', '>', 0);
                })
                ->get()
                ->map(function($partitura) {
                    try {
                        $instrumentos = $partitura->inventarios
                            ->where('cantidad_disponible', '>', 0)
                            ->pluck('instrumento')
                            ->unique()
                            ->values();
                        
                        $cantidadTotal = $partitura->inventarios
                            ->where('cantidad_disponible', '>', 0)
                            ->sum('cantidad_disponible');
                        
                        // Get autor from obra -> contribuciones -> autor
                        $autor = 'Autor desconocido';
                        if ($partitura->obra && $partitura->obra->contribuciones && $partitura->obra->contribuciones->isNotEmpty()) {
                            $firstContribucion = $partitura->obra->contribuciones->first();
                            if ($firstContribucion && $firstContribucion->autor) {
                                $autor = trim($firstContribucion->autor->nombre . ' ' . $firstContribucion->autor->apellido);
                            }
                        }
                        
                        return [
                            'id' => $partitura->id,
                            'titulo' => $partitura->obra->titulo ?? 'Título desconocido',
                            'autor' => $autor,
                            'instrumentos' => $instrumentos,
                            'cantidad_disponible' => $cantidadTotal
                        ];
                    } catch (\Exception $e) {
                        \Log::warning('Error processing individual partitura: ' . $e->getMessage());
                        return [
                            'id' => $partitura->id,
                            'titulo' => $partitura->obra->titulo ?? 'Título desconocido',
                            'autor' => 'Autor desconocido',
                            'instrumentos' => [],
                            'cantidad_disponible' => 0
                        ];
                    }
                });
            
            return response()->json([
                'success' => true,
                'partituras' => $partituras
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in partiturasDisponibles: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar partituras disponibles',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * API: Submit loan request
     */
 /*   public function solicitarPrestmo(Request $request)
    {
    dd('solicitarPrestamo alcanzado');    
        try {
            $request->validate([
                'partitura_id' => 'required|exists:partituras,id',
                'instrumento' => 'required|string',
                'cantidad' => 'required|integer|min:1'
            ]);

            $user = Auth::user();
            
            // Check if user has required role
            if (!in_array($user->role, ['loan_user', 'loan_user']) && $user->email !== 'mafer2@example.com') {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permisos para solicitar préstamos'
                ], 403);
            }

            // Check available stock

            // Create loan request
            $prestamo = Prestamo::create([
                'usuario_inventario_id' => $user->id,
                'inventario_id' => $inventario->id,
                'cantidad' => $request->cantidad,
                'estado' => 'Pendiente',
                'fecha_prestamo' => now(),
                'descripcion' => "Solicitud de préstamo para {$request->instrumento}"
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de préstamo enviada exitosamente',
                'prestamo' => [
                    'id' => $prestamo->id,
                    'estado' => $prestamo->estado,
                    'fecha_prestamo' => $prestamo->fecha_prestamo
                ]
            ]);


        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error in solicitarPrestamo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud de préstamo'
            ], 500);
        }
    }*/

public function solicitarPrestamo(Request $request)
{
    // 1) Validar lo que llega del front
    $v = $request->validate([
        'partitura_id' => 'required|integer',
        'instrumento'  => 'required|string|max:255',
        'cantidad'     => 'required|integer|min:1',
        'titulo'       => 'nullable|string|max:255', // viene del front como 'titulo'
        'email'        => 'nullable|email',          // si no viene, usamos el del auth
    ]);

    $user  = Auth::user();
    $email = $request->input('email', $user?->email);
    if (!$email) {
        return response()->json([
            'success' => false,
            'message' => 'No se pudo determinar el email del usuario.'
        ], 422);
    }

    // 2) Armar payload para CONSULTA con TODOS los campos
    $obraTitulo = $request->input('titulo'); // ya te llega del front
    $payload = [
        'email'        => $email,
        'partitura_id' => (int) $v['partitura_id'],
        'instrumento'  => (string) $v['instrumento'],
        'cantidad'     => (int) $v['cantidad'],

        // nuevos (consulta los guardará):
        'obra_titulo'      => $obraTitulo,                      // título de la obra
        'titulo'           => $obraTitulo,                      // compat
        'fecha_prestamo'   => now()->format('Y-m-d H:i:s'),     // o ISO si prefieres
        'fecha_devolucion' => null,                             // inicio sin devolución
        'estado'           => 'Pendiente',
    ];

    // 3) Enviar a CONSULTA (sin crear espejo local)
    try {
        $resp = Http::acceptJson()
            ->post('http://localhost:8050/api/v1/solicitar-prestamo', $payload);
    } catch (\Throwable $e) {
        \Log::error('Error llamando a consulta: '.$e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Servidor de consulta no disponible.'
        ], 502);
    }

    if ($resp->failed()) {
        return response()->json([
            'success' => false,
            'message' => $resp->json('message') ?? 'Error en servidor de consulta',
            'errors'  => $resp->json('errors') ?? null,
        ], $resp->status());
    }

    return response()->json([
        'success'  => true,
        'message'  => $resp->json('message') ?? 'Préstamo creado en consulta',
        'prestamo' => $resp->json('prestamo') ?? null,
    ], 201);
}
    /**
     * API: Get user's loan history
     */
public function misPrestamos()
{
    
    $user = Auth::user();
    if (!$user) {
        return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
    }

    try {
        // Llamada a CONSULTA (server-side, sin CORS en el navegador)
        $resp = Http::acceptJson()->get('http://127.0.0.1:8050/api/v1/prestamosdata');
    } catch (\Throwable $e) {
        \Log::error('consulta/prestamosdata error: '.$e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Servidor de consulta no disponible.'
        ], 502);
    }

    if ($resp->failed()) {
        return response()->json([
            'success' => false,
            'message' => $resp->json('message') ?? 'Error en servidor de consulta'
        ], $resp->status());
    }

    $raw  = $resp->json();
    $rows = $raw['prestamos'] ?? $raw['data'] ?? [];

    // Filtrar por el email del usuario autenticado
    $mine = array_values(array_filter($rows, function ($p) use ($user) {
        $u = $p['usuario__inventario'] ?? $p['usuario_inventario'] ?? null;
        $correo = $u['correo'] ?? $u['email'] ?? null;
        return $correo && strcasecmp($correo, $user->email) === 0;
    }));

    // Adaptar a las 3 columnas que renderiza tu front
    $prestamos = array_map(function ($p) {
        $obra = $p['obra_titulo'] ?? ($p['partitura']['obra']['titulo'] ?? 'N/A');
        $cantidad = isset($p['cantidad'])
            ? (int)$p['cantidad']
            : (isset($p['cantidad_solicitada']) ? (int)$p['cantidad_solicitada'] : 1);
        $estado = ucfirst($p['estado'] ?? 'Pendiente');

        return [
            'obra_titulo'         => $obra,
            'cantidad_solicitada' => $cantidad,
            'estado'              => $estado,
        ];
    }, $mine);

    return response()->json([
        'success'   => true,
        'prestamos' => $prestamos,
    ]);
}


}