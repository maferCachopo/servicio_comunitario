<?php

namespace App\Http\Controllers;

use App\Models\Obra;
use App\Models\Autor;
use App\Models\Contribucion;
use App\Models\TipoContribucion;
use App\Models\Inventario;
use App\Models\Prestamo;
use App\Models\User;
use App\Models\Partitura;
use App\Models\Estante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class InventoryController extends Controller
{
    /**
     * Muestra la página de inventario con pestañas dinámicas.
     */
    public function index()
    {
        return view('inventory.index');
    }

    /**
     * Obtiene datos para DataTables - Historial de Partituras.
     */
    public function getPartiturasData(Request $request)
    {
        // 1. Realizar la petición a la API y decodificar la respuesta a un objeto PHP
        $response = Http::get('http://127.0.0.1:8050/api/v1/partiturasdata')->object();
        
        // Si la respuesta no tiene la estructura esperada, devolvemos una respuesta vacía para evitar errores
        if (!isset($response->inventarios)) {
            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }

        // 2. Extraer los datos principales de la respuesta
        $inventarios = $response->inventarios;
        $totalRecords = $response->totalRecords;
        $filteredRecords = $response->filteredRecords;

        // 3. Formatear los datos para que DataTables los entienda
        $data = [];
        foreach ($inventarios as $inventario) {
            
            // --- INICIO DE CORRECCIONES ---

            // A. Definir variables para los objetos anidados. Esto soluciona los errores de "Undefined variable".
            $partitura = $inventario->partitura;
            $obra = $partitura->obra;
            $estante = $inventario->estante;

            // B. Procesar el array de "contribuciones" para obtener TODOS los autores y sus roles.
            $autores = [];
            $tiposContribucion = [];
            if (!empty($obra->contribuciones)) {
                foreach ($obra->contribuciones as $contribucion) {
                    // Añadir el nombre del autor si existe
                    if (isset($contribucion->autor->nombre)) {
                        $autores[] = $contribucion->autor->nombre;
                    }
                    // Añadir el tipo de contribución si existe
                    if (isset($contribucion->tipo_contribucion->nombre_contribucion)) {
                        $tiposContribucion[] = $contribucion->tipo_contribucion->nombre_contribucion;
                    }
                }
            }

            // C. Crear las variables $listaAutores y $listaTiposContribucion. Esto soluciona el error original.
            $listaAutores = !empty($autores) ? implode(', ', $autores) : 'N/A';
            $listaTiposContribucion = !empty($tiposContribucion) ? implode(', ', $tiposContribucion) : 'N/A';

            // --- FIN DE CORRECCIONES ---

            // D. Construir el array de datos usando las variables correctas
            $data[] = [
                'titulo' => $obra->titulo ?? 'N/A',
                'autor' => $listaAutores,
                'tipo_contribucion' => $listaTiposContribucion,
                'anio' => $obra->anio ?? 'N/A',
                // Corregido: Usar la variable $partitura que definimos arriba
                'instrumento' => $partitura->instrumento->nombre ?? 'N/A',
                // Corregido: 'Cantidad' debe ser con 'C' mayúscula como en el JSON
                'cantidad' => $inventario->Cantidad ?? 0,
                'cantidad_disponible' => 'N/A', // Este dato no viene en el JSON
                // Corregido: Usar la variable $estante que definimos arriba
                'gaveta' => $estante->gaveta ?? 'N/A',
                // Corregido: Usar $estante también en el botón
                'acciones' => '<button class="btn btn-sm btn-primary edit-inventario-btn"
                    data-partitura-id="' . $inventario->partitura_id . '" 
                    data-estante-id="' . $inventario->estante_id . '"
                    data-cantidad="' . ($inventario->Cantidad ?? 0) . '"
                    data-cantidad-disponible="N/A"
                    data-gaveta="' . ($estante->gaveta ?? '') . '">
                    <i class="fas fa-edit"></i> Editar
                </button>'
            ];
        }

        // 4. Devolver la respuesta en el formato que espera DataTables
        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

        /**
         * Obtiene datos para DataTables - Historial de Préstamos.
            */
    public function getPrestamosData(Request $request)
    {
        // Pasamos los parámetros de DataTables (búsqueda, orden, etc.) a la API
        $response = Http::get('http://127.0.0.1:8050/api/v1/prestamosdata', $request->all())->object();

        if (!isset($response->prestamos)) {
            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }

        $prestamos = $response->prestamos;
        $totalRecords = $response->totalRecords;
        $filteredRecords = $response->filteredRecords;
        
        // Formatear datos para DataTables
        $data = [];
        foreach ($prestamos as $prestamo) {
            
            // --- INICIO DE CORRECCIONES ---
            // Accedemos a los datos usando las rutas correctas del JSON
            
            $obraTitulo = $prestamo->partitura->obra->titulo ?? 'N/A';
            $instrumento = $prestamo->partitura->instrumento->nombre ?? 'N/A';
            
            // NOTA: Laravel convierte el nombre de la relación 'Usuario_Inventario' a 'usuario__inventario' en el JSON.
            $usuario = $prestamo->usuario__inventario ?? null;
            $usuarioNombre = $usuario->nombre ?? 'N/A';
            $usuarioEmail = $usuario->correo ?? 'N/A'; // Tu JSON usa 'correo'
            
            $data[] = [
                'id' => $prestamo->id,
                'obra_titulo' => $obraTitulo,
                'usuario_nombre' => $usuarioNombre,
                'usuario_email' => $usuarioEmail,
                'instrumento' => $instrumento,
                'cantidad' => $prestamo->cantidad ?? 1,
                // Usamos 'created_at' como la fecha del préstamo
                'fecha_prestamo' => \Carbon\Carbon::parse($prestamo->created_at)->format('d/m/Y H:i'),
                // La lógica para la fecha de devolución ya estaba bien, solo necesitaba los datos correctos
                'fecha_devolucion' => $prestamo->fecha_devolucion 
                                        ? \Carbon\Carbon::parse($prestamo->fecha_devolucion)->format('d/m/Y H:i') 
                                        : 'No devuelto',
                'estado' => ucfirst($prestamo->estado),
                'descripcion' => $prestamo->descripcion ?? 'Sin descripción',
                'acciones' => '<button class="btn btn-sm btn-info view-btn" data-id="'.$prestamo->id.'">Ver</button>' // Ejemplo
            ];
            
            // --- FIN DE CORRECCIONES ---
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    /**
     * API: Get pending loan requests for admin
     */
    public function prestamosPendientes()
    {
        try {
            $prestamos = Prestamo::with(['inventario.partitura.obra.contribuciones.autor', 'user'])
                ->where('estado', 'Pendiente')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($prestamo) {
                    // Get autor from obra -> contribuciones -> autor
                    $autor = 'Autor desconocido';
                    if ($prestamo->inventario && $prestamo->inventario->partitura &&
                        $prestamo->inventario->partitura->obra &&
                        $prestamo->inventario->partitura->obra->contribuciones->isNotEmpty()) {
                        $firstContribucion = $prestamo->inventario->partitura->obra->contribuciones->first();
                        if ($firstContribucion && $firstContribucion->autor) {
                            $autor = $firstContribucion->autor->nombre . ' ' . $firstContribucion->autor->apellido;
                        }
                    }
                    
                    return [
                        'id' => $prestamo->id,
                        'usuario' => [
                            'id' => $prestamo->user->id,
                            'nombre' => $prestamo->user->name,
                            'email' => $prestamo->user->email
                        ],
                        'partitura' => [
                            'id' => $prestamo->inventario->partitura->id ?? null,
                            'titulo' => $prestamo->inventario->partitura->obra->titulo ?? 'Título desconocido',
                            'autor' => $autor
                        ],
                        'instrumento' => $prestamo->inventario->instrumento ?? 'Instrumento desconocido',
                        'cantidad' => $prestamo->cantidad,
                        'fecha_solicitud' => $prestamo->fecha_solicitud,
                        'created_at' => $prestamo->created_at
                    ];
                });
            
            return response()->json([
                'success' => true,
                'prestamos' => $prestamos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar préstamos pendientes'
            ], 500);
        }
    }

    /**
     * API: Process loan request (approve/reject)
     */
    public function procesarPrestamo(Request $request, $id)
    {
        try {
            $request->validate([
                'accion' => 'required|in:aceptar,rechazar'
            ]);

            $prestamo = Prestamo::with(['partitura', 'user'])->findOrFail($id);
            
            // Check if already processed
            if ($prestamo->estado !== 'Pendiente') {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta solicitud ya fue procesada'
                ], 400);
            }

            $accion = $request->accion;
            $prestamo->estado = $accion === 'aceptar' ? 'Aceptado' : 'Rechazado';
            $prestamo->fecha_respuesta = now();
            $prestamo->save();

            // If approved, update inventory
            if ($accion === 'aceptar') {
                $inventario = $prestamo->inventario;
                
                if ($inventario && $inventario->cantidad_disponible >= $prestamo->cantidad) {
                    $inventario->cantidad_disponible -= $prestamo->cantidad;
                    $inventario->save();
                } else {
                    // Rollback if not enough stock
                    $prestamo->estado = 'Pendiente';
                    $prestamo->fecha_respuesta = null;
                    $prestamo->save();
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Stock insuficiente al momento de procesar'
                    ], 400);
                }
            }

            // Here you would typically send notifications
            // For now, we'll just return success

            return response()->json([
                'success' => true,
                'message' => 'Solicitud ' . ($accion === 'aceptar' ? 'aceptada' : 'rechazada') . ' exitosamente',
                'prestamo' => [
                    'id' => $prestamo->id,
                    'estado' => $prestamo->estado,
                    'fecha_respuesta' => $prestamo->fecha_respuesta
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud'
            ], 500);
        }
    }

    /**
     * Update inventory record (quantity and location)
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'cantidad' => 'required|integer|min:1',
                'gaveta' => 'required|string|max:255'
            ]);

            return $response = Http::withHeaders([
               'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                    ])->put('http://127.0.0.1:8050/api/v1/prestamosupdate/'.$id, $validated)->json();

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el inventario: ' . $e->getMessage()
            ], 500);
        }
    }
}