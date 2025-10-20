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
        $query = Inventario::with(['partitura.obra.contribuciones.autor', 'partitura.obra.contribuciones.tipoContribucion', 'estante'])
            ->select('inventarios.*')
            ->whereHas('partitura.obra')
            ->where('cantidad', '>', 0);

        // Búsqueda global
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                // Search in obra data
                $q->whereHas('partitura.obra', function($q) use ($search) {
                    $q->where('titulo', 'like', "%{$search}%")
                      ->orWhere('anio', 'like', "%{$search}%");
                })
                  ->orWhereHas('partitura.obra.contribuciones.autor', function($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%")
                        ->orWhere('apellido', 'like', "%{$search}%");
                  })
                  ->orWhereHas('partitura.obra.contribuciones.tipoContribucion', function($q) use ($search) {
                      $q->where('nombre_contribucion', 'like', "%{$search}%");
                  })
                  ->orWhere('instrumento', 'like', "%{$search}%")
                  ->orWhereHas('estante', function($q) use ($search) {
                      $q->where('gaveta', 'like', "%{$search}%");
                  });
            });
        }

        // Ordenamiento
        if ($request->has('order')) {
            $columns = ['titulo', 'autor', 'tipo_contribucion', 'anio', 'instrumento', 'cantidad', 'gaveta'];
            $column = $columns[$request->order[0]['column']] ?? 'titulo';
            $direction = $request->order[0]['dir'] ?? 'asc';
            
            if ($column === 'autor') {
                $query->join('partituras', 'inventarios.partitura_id', '=', 'partituras.id')
                      ->join('obras', 'partituras.obra_id', '=', 'obras.id')
                      ->join('contribuciones', 'obras.id', '=', 'contribuciones.obra_id')
                      ->join('autores', 'contribuciones.autor_id', '=', 'autores.id')
                      ->orderBy('autores.nombre', $direction)
                      ->select('inventarios.*');
            } elseif ($column === 'tipo_contribucion') {
                $query->join('partituras', 'inventarios.partitura_id', '=', 'partituras.id')
                      ->join('obras', 'partituras.obra_id', '=', 'obras.id')
                      ->join('contribuciones', 'obras.id', '=', 'contribuciones.obra_id')
                      ->join('tipo_contribuciones', 'contribuciones.tipo_contribucion_id', '=', 'tipo_contribuciones.id')
                      ->orderBy('tipo_contribuciones.nombre_contribucion', $direction)
                      ->select('inventarios.*');
            } elseif ($column === 'titulo' || $column === 'anio') {
                $query->join('partituras', 'inventarios.partitura_id', '=', 'partituras.id')
                      ->join('obras', 'partituras.obra_id', '=', 'obras.id')
                      ->orderBy("obras.$column", $direction)
                      ->select('inventarios.*');
            } elseif ($column === 'gaveta') {
                $query->join('estantes', 'inventarios.estante_id', '=', 'estantes.id')
                      ->orderBy('estantes.gaveta', $direction)
                      ->select('inventarios.*');
            } else {
                $query->orderBy($column, $direction);
            }
        }

        $totalRecords = Inventario::where('cantidad', '>', 0)->count();
        $filteredRecords = $query->count();

        // Paginación
        $inventarios = $query->skip($request->start ?? 0)
            ->take($request->length ?? 10)
            ->get();

        // Formatear datos para DataTables - now showing multiple instrumentations per score
        $data = [];
        foreach ($inventarios as $inventario) {
            $obra = $inventario->partitura->obra;
            $autor = $obra->contribuciones->first()->autor ?? null;
            $tipoContribucion = $obra->contribuciones->first()->tipoContribucion ?? null;
            
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
        $query = Prestamo::with(['inventario.partitura.obra', 'user'])
            ->select('prestamos.*')
            ->orderBy('fecha_prestamo', 'desc');

        // Búsqueda global
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('inventario.partitura.obra', function($q) use ($search) {
                      $q->where('titulo', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        // Ordenamiento
        if ($request->has('order')) {
            $columns = ['id', 'obra_titulo', 'instrumento', 'cantidad', 'usuario_nombre', 'usuario_email', 'fecha_prestamo', 'descripcion'];
            $column = $columns[$request->order[0]['column']] ?? 'id';
            $direction = $request->order[0]['dir'] ?? 'asc';
            
            if ($column === 'obra_titulo') {
                $query->join('inventarios', 'prestamos.inventario_id', '=', 'inventarios.id')
                      ->join('partituras', 'inventarios.partitura_id', '=', 'partituras.id')
                      ->join('obras', 'partituras.obra_id', '=', 'obras.id')
                      ->orderBy('obras.titulo', $direction)
                      ->select('prestamos.*');
            } elseif ($column === 'instrumento') {
                $query->join('inventarios', 'prestamos.inventario_id', '=', 'inventarios.id')
                      ->orderBy('inventarios.instrumento', $direction)
                      ->select('prestamos.*');
            } elseif ($column === 'usuario_nombre' || $column === 'usuario_email') {
                $query->join('users', 'prestamos.user_id', '=', 'users.id')
                      ->orderBy($column === 'usuario_nombre' ? 'users.name' : 'users.email', $direction)
                      ->select('prestamos.*');
            } else {
                $query->orderBy($column, $direction);
            }
        }

        $totalRecords = Prestamo::count();
        $filteredRecords = $query->count();

        // Paginación
        $prestamos = $query->skip($request->start ?? 0)
            ->take($request->length ?? 10)
            ->get();

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
            'data' => $data
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

            // Find the inventory record
            $inventario = Inventario::findOrFail($id);

            // Find or create the estante with the new gaveta
            $estante = Estante::firstOrCreate(
                ['gaveta' => trim($validated['gaveta'])],
                ['gaveta' => trim($validated['gaveta'])]
            );

            // Update the inventory
            $inventario->cantidad = $validated['cantidad'];
            $inventario->estante_id = $estante->id;
            
            // Adjust available quantity if total quantity changed
            $quantityDifference = $validated['cantidad'] - $inventario->getOriginal('cantidad');
            if ($quantityDifference > 0) {
                // If total increased, increase available by the same amount
                $inventario->cantidad_disponible += $quantityDifference;
            } elseif ($quantityDifference < 0) {
                // If total decreased, ensure available doesn't exceed total
                $inventario->cantidad_disponible = min($inventario->cantidad_disponible, $validated['cantidad']);
            }
            
            $inventario->save();

            return response()->json([
                'success' => true,
                'message' => 'Inventario actualizado exitosamente'
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
                'message' => 'Error al actualizar el inventario: ' . $e->getMessage()
            ], 500);
        }
    }
}