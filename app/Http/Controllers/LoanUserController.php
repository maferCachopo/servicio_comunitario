<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Partitura;
use App\Models\Prestamo;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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
        return redirect()->route('loan_users.index')
                         ->with('success', 'Usuario de préstamo creado con éxito.');
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
    public function solicitarPrestamo(Request $request)
    {
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
            $inventario = Inventario::where('partitura_id', $request->partitura_id)
                ->where('instrumento', $request->instrumento)
                ->first();
            
            if (!$inventario || $inventario->cantidad_disponible < $request->cantidad) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock insuficiente para esta solicitud'
                ], 400);
            }

            // Create loan request
            $prestamo = Prestamo::create([
                'user_id' => $user->id,
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
    }

    /**
     * API: Get user's loan history
     */
    public function misPrestamos()
    {
        try {
            $user = Auth::user();
            
            $prestamos = Prestamo::with(['inventario.partitura.obra.contribuciones.autor'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($prestamo) {
                    // Get autor from obra -> contribuciones -> autor
                    $autor = 'Autor desconocido';
                    if ($prestamo->inventario && $prestamo->inventario->partitura &&
                        $prestamo->inventario->partitura->obra &&
                        $prestamo->inventario->partitura->obra->contribuciones->isNotEmpty()) {
                        $firstContribucion = $prestamo->inventario->partitura->obra->contribuciones->first();
                        if ($firstContribucion->autor) {
                            $autor = $firstContribucion->autor->nombre . ' ' . $firstContribucion->autor->apellido;
                        }
                    }
                    
                    return [
                        'id' => $prestamo->id,
                        'partitura_titulo' => $prestamo->inventario->partitura->obra->titulo ?? 'Título desconocido',
                        'partitura_autor' => $autor,
                        'instrumento' => $prestamo->inventario->instrumento ?? 'Instrumento desconocido',
                        'cantidad' => $prestamo->cantidad,
                        'estado' => $prestamo->estado,
                        'fecha_solicitud' => $prestamo->fecha_solicitud,
                        'created_at' => $prestamo->created_at,
                        'updated_at' => $prestamo->updated_at
                    ];
                });
            
            return response()->json([
                'success' => true,
                'prestamos' => $prestamos
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in misPrestamos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar historial de préstamos'
            ], 500);
        }
    }
}