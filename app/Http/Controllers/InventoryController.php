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
        // Query to get all inventarios with their related data, showing multiple instrumentations per score

        $response = Http::get('http://127.0.0.1:8050/api/v1/partiturasdata')->object();

        $inventarios = $response->inventarios;
        $totalRecords = $response->totalRecords;
        $filteredRecords = $response->filteredRecords;

        // Formatear datos para DataTables - now showing multiple instrumentations per score
        $data = [];
        foreach ($inventarios as $inventario) {
            $obra = $inventario->partitura->obra;
            $autor = $obra->contribuciones[0]->autor ?? null;
            $tipoContribucion = $obra->contribuciones[0]->tipoContribucion ?? null;
            
            $data[] = [
                'titulo' => $obra->titulo,
                'autor' => $autor ? $autor->nombre . ' ' . $autor->apellido : 'N/A',
                'tipo_contribucion' => $tipoContribucion ? $tipoContribucion->nombre_contribucion : 'N/A',
                'anio' => $obra->anio,
                'instrumento' => $inventario->instrumento ?? 'N/A',
                'cantidad' => $inventario->cantidad,
                'cantidad_disponible' => $inventario->cantidad_disponible,
                'gaveta' => $inventario->estante ? $inventario->estante->gaveta : 'N/A',
                'acciones' => '<button class="btn btn-sm btn-primary edit-inventario-btn"
                    data-id="' . $inventario->id . '"
                    data-cantidad="' . $inventario->cantidad . '"
                    data-cantidad-disponible="' . $inventario->cantidad_disponible . '"
                    data-estante-id="' . $inventario->estante_id . '"
                    data-gaveta="' . ($inventario->estante ? $inventario->estante->gaveta : '') . '">
                    <i class="fas fa-edit"></i> Editar
                </button>'
            ];
        }

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
         $response = Http::get('http://127.0.0.1:8050/api/v1/prestamosdata')->object();

        $prestamos = $response->prestamos;
        $totalRecords = $response->totalRecords;
        $filteredRecords = $response->filteredRecords;
         
        // Formatear datos para DataTables
        $data = [];
        foreach ($prestamos as $prestamo) {
            $obraTitulo = $prestamo->inventario->partitura->obra->titulo ?? 'N/A';
            $usuarioNombre = $prestamo->user->name ?? 'N/A';
            $usuarioEmail = $prestamo->user->email ?? 'N/A';
            $instrumento = $prestamo->inventario->instrumento ?? 'N/A';
            
            $data[] = [
                'id' => $prestamo->id,
                'obra_titulo' => $obraTitulo,
                'usuario_nombre' => $usuarioNombre,
                'usuario_email' => $usuarioEmail,
                'instrumento' => $instrumento,
                'cantidad' => $prestamo->cantidad ?? 1,
                'fecha_prestamo' => \Carbon\Carbon::parse($prestamo->fecha_prestamo)->format('d/m/Y H:i'),
                'fecha_devolucion' => $prestamo->fecha_devolucion ? \Carbon\Carbon::parse($prestamo->fecha_devolucion)->format('d/m/Y H:i') : 'No devuelto',
                'estado' => ucfirst($prestamo->estado),
                'descripcion' => $prestamo->descripcion ?? 'Sin descripción'
            ];
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
            'Cosa1' => $response->Cosa1,
            'Cosa2' => $request
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