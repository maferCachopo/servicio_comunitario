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
        $query = Obra::with(['contribuciones.autor', 'contribuciones.tipoContribucion', 'partituras.inventarios.estante'])
            ->select('obras.*');

        // Búsqueda global
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('obras.titulo', 'like', "%{$search}%")
                  ->orWhere('obras.anio', 'like', "%{$search}%")
                  ->orWhereHas('contribuciones.autor', function($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%")
                        ->orWhere('apellido', 'like', "%{$search}%");
                  })
                  ->orWhereHas('contribuciones.tipoContribucion', function($q) use ($search) {
                      $q->where('nombre_contribucion', 'like', "%{$search}%");
                  });
            });
        }

        // Ordenamiento
        if ($request->has('order')) {
            $columns = ['titulo', 'autor', 'tipo_contribucion', 'anio', 'cantidad', 'gaveta'];
            $column = $columns[$request->order[0]['column']] ?? 'titulo';
            $direction = $request->order[0]['dir'] ?? 'asc';
            
            if ($column === 'autor') {
                $query->join('contribuciones', 'obras.id', '=', 'contribuciones.obra_id')
                      ->join('autores', 'contribuciones.autor_id', '=', 'autores.id')
                      ->orderBy('autores.nombre', $direction)
                      ->select('obras.*');
            } elseif ($column === 'tipo_contribucion') {
                $query->join('contribuciones', 'obras.id', '=', 'contribuciones.obra_id')
                      ->join('tipo_contribuciones', 'contribuciones.tipo_contribucion_id', '=', 'tipo_contribuciones.id')
                      ->orderBy('tipo_contribuciones.nombre_contribucion', $direction)
                      ->select('obras.*');
            } else {
                $query->orderBy($column, $direction);
            }
        }

        $totalRecords = Obra::count();
        $filteredRecords = $query->count();

        // Paginación
        $partituras = $query->skip($request->start ?? 0)
            ->take($request->length ?? 10)
            ->get();

        // Formatear datos para DataTables
        $data = [];
        foreach ($partituras as $obra) {
            $autor = $obra->contribuciones->first()->autor ?? null;
            $tipoContribucion = $obra->contribuciones->first()->tipoContribucion ?? null;
            $inventario = $obra->partituras->first()->inventarios->first() ?? null;
            
            $data[] = [
                'titulo' => $obra->titulo,
                'autor' => $autor ? $autor->nombre . ' ' . $autor->apellido : 'N/A',
                'tipo_contribucion' => $tipoContribucion ? $tipoContribucion->nombre_contribucion : 'N/A',
                'anio' => $obra->anio,
                'cantidad' => $inventario ? $inventario->cantidad : 0,
                'gaveta' => $inventario && $inventario->estante ? $inventario->estante->gaveta : 'N/A',
                'acciones' => '<button class="btn btn-sm btn-secondary" disabled>Acciones</button>'
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
            ->select('prestamos.*');

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
            $columns = ['id', 'obra_titulo', 'usuario_nombre', 'usuario_email', 'fecha_prestamo', 'descripcion'];
            $column = $columns[$request->order[0]['column']] ?? 'id';
            $direction = $request->order[0]['dir'] ?? 'asc';
            
            if ($column === 'obra_titulo') {
                $query->join('inventarios', 'prestamos.inventario_id', '=', 'inventarios.id')
                      ->join('partituras', 'inventarios.partitura_id', '=', 'partituras.id')
                      ->join('obras', 'partituras.obra_id', '=', 'obras.id')
                      ->orderBy('obras.titulo', $direction)
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
            
            $data[] = [
                'id' => $prestamo->id,
                'obra_titulo' => $obraTitulo,
                'usuario_nombre' => $usuarioNombre,
                'usuario_email' => $usuarioEmail,
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
            $prestamos = Prestamo::with(['partitura.autor', 'user'])
                ->where('estado', 'Pendiente')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($prestamo) {
                    return [
                        'id' => $prestamo->id,
                        'usuario' => [
                            'id' => $prestamo->user->id,
                            'nombre' => $prestamo->user->name,
                            'email' => $prestamo->user->email
                        ],
                        'partitura' => [
                            'id' => $prestamo->partitura->id,
                            'titulo' => $prestamo->partitura->titulo,
                            'autor' => $prestamo->partitura->autor->nombre ?? 'Autor desconocido'
                        ],
                        'instrumento' => $prestamo->instrumento,
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
                $inventario = Inventario::where('partitura_id', $prestamo->partitura_id)
                    ->where('instrumento', $prestamo->instrumento)
                    ->first();
                
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
}