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
    // 1) Leer y validar el payload del JS
    $v = $request->validate([
        'partitura_id' => 'required|integer',
        'instrumento'  => 'required|string|max:255',
        'cantidad'     => 'required|integer|min:1',
        'titulo'       => 'nullable|string|max:255',
    ]);


    $user = Auth::user();

    // 2) Construir el payload remoto (desde servidor)
    $remotePayload = [
        'email'        => $user->email,                 // o tu usuario_inventario_id mapeado
        'partitura_id' => (int) $v['partitura_id'],
        'instrumento'  => (string) $v['instrumento'],
        'cantidad'     => (int) $v['cantidad'],
    ];

    // 3) Llamar a consulta
    $response = Http::acceptJson()->post('http://localhost:8050/api/v1/solicitar-prestamo', $remotePayload);
    if ($response->failed()) {
        return response()->json(['success'=>false,'message'=>'No se pudo crear el préstamo en consulta'], 502);
    }

    $inventarioId = \App\Models\Inventario::where('partitura_id', $request->partitura_id)
    ->where('instrumento', $request->instrumento)
    ->value('id');

    // Título de la obra (preferir lo que manda el front)
    $obraTitulo = $request->input('titulo')                          // 👈 PRIMERO 'titulo'
               ?? $request->input('obra_titulo')
               ?? $request->input('nombre_de_partitura');

    if (!$obraTitulo) {
            try {
                $obj = Http::get('http://127.0.0.1:8050/api/v1/partiturasdata')->object();
                if ($obj && isset($obj->inventarios)) {
                    foreach ($obj->inventarios as $inv) {
                        $pid = $inv->partitura_id ?? ($inv->partitura->id ?? null);
                        if ((int)$pid === (int)$v['partitura_id']) {
                            $obraTitulo = $inv->partitura->obra->titulo ?? ($inv->obra->titulo ?? null);
                            break;
                        }
                    }   
                }
            } catch (\Throwable $e) {
                // silencioso: si falla, queda null y no rompe
            }
        }

    // Fallback al placeholder 1 (asegúrate de que ese inventario exista en DB)
    $inventarioId = $inventarioId ?: 1;
    
    Prestamo::create([
        'inventario_id'    => $inventarioId,
        'partitura_id'     => (int) $request->partitura_id, // ← referencia “blanda” a consulta
        'user_id'          => auth()->id(),
        'cantidad'         => (int) $request->cantidad,

        'estado'           => 'Pendiente',
        'fecha_prestamo'   => now(),
        'fecha_devolucion' => null,
        'descripcion'      => "Solicitud de préstamo para instrumento {$request->instrumento}",
        'obra_titulo'      => $obraTitulo,
    ]);

    return response()->json(['success'=>true,'message'=>'OK'], 201);
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

    $rows = Prestamo::where('user_id', $user->id)
        // evita ordenar por una columna que pueda venir inválida
        ->orderByDesc('created_at')
        ->get();

    $prestamos = $rows->map(function ($p) {

        // Título: usa el campo local; si no, intenta extraerlo de la descripción
        $titulo = $p->obra_titulo;
        if (!$titulo && $p->descripcion) {
            $titulo = preg_replace('/^Solicitud de pr(é|e)stamo para\s*/iu', '', $p->descripcion);
        }

        return [
            'obra_titulo'         => $titulo ?: 'N/A',
            'cantidad_solicitada' => (int)($p->cantidad ?? 1),
            'estado'              => ucfirst($p->estado ?? 'Pendiente'),
        ];
    });

    return response()->json([
        'success'   => true,
        'prestamos' => $prestamos,
    ]);
}

}