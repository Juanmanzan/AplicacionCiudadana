<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmergenciaApiController extends Controller
{
    /**
     * ✅ Detalle de una emergencia por ID
     * GET /api/emergencias/{id}
     */
    public function show($id)
    {
        $row = DB::table('emergencia')
            ->selectRaw('
                id_emergencia,
                cedula_usuario,
                id_tipo_emergencia,
                descripcion,
                estado,
                fecha_hora,
                origen,
                ST_Y(ubicacion::geometry) AS lat,
                ST_X(ubicacion::geometry) AS lng
            ')
            ->where('id_emergencia', $id)
            ->first();

        if (!$row) {
            return response()->json([
                'ok' => false,
                'message' => 'Emergencia no encontrada'
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'data' => $row
        ]);
    }

    /**
     * Historial por cédula con filtros por:
     * - estado (PENDIENTE, ATENDIDA, etc) 
     * - tipo   (id_tipo_emergencia)       
     * - fecha exacta (YYYY-MM-DD)         
     * - desde / hasta (YYYY-MM-DD)        
     * - limit (1..2000)                   
     *
     * GET /api/usuarios/{cedula}/emergencias?estado=&tipo=&fecha=&desde=&hasta=&limit=
     */

    public function miHistorial(Request $request)
    {
        $user = $request->user();
        return $this->historialBase($request, $user->cedula);
    }


    public function historialBase(Request $request, string $cedula)
    {
        $estado = $request->query('estado'); // PENDIENTE, ATENDIDA, etc
        $tipo   = $request->query('tipo');   // 1,2,3,4...
        $fecha  = $request->query('fecha');  // YYYY-MM-DD (día exacto)
        $desde  = $request->query('desde');  // YYYY-MM-DD
        $hasta  = $request->query('hasta');  // YYYY-MM-DD
        $limit  = (int) $request->query('limit', 500);

        $q = DB::table('emergencia')
            ->selectRaw('
                id_emergencia,
                cedula_usuario,
                id_tipo_emergencia,
                descripcion,
                estado,
                fecha_hora,
                origen,
                ST_Y(ubicacion::geometry) AS lat,
                ST_X(ubicacion::geometry) AS lng
            ')
            ->where('cedula_usuario', $cedula)
            ->orderByDesc('fecha_hora');

        // Estado
        if ($estado) {
            $q->whereRaw('UPPER(estado) = ?', [strtoupper($estado)]);
        }

        // Tipo de emergencia (opcional)
        if ($tipo !== null && $tipo !== '') {
            $q->where('id_tipo_emergencia', (int) $tipo);
        }

        /**
         * Filtros de fecha:
         * - Si viene "fecha=YYYY-MM-DD" → filtra solo ese día.
         * - Si viene "desde/hasta" → filtra rango inclusivo por día.
         *
         * NOTA: usamos DATE(fecha_hora) para comparar por día (sin hora).
         */
        if ($fecha) {
            $q->whereRaw('DATE(fecha_hora) = ?', [$fecha]);
        } else {
            if ($desde) {
                $q->whereRaw('DATE(fecha_hora) >= ?', [$desde]);
            }
            if ($hasta) {
                $q->whereRaw('DATE(fecha_hora) <= ?', [$hasta]);
            }
        }

        $limit = max(1, min($limit, 2000));
        $rows = $q->limit($limit)->get();

        return response()->json([
            'ok' => true,
            'cedula_usuario' => $cedula,
            'filters' => [
                'estado' => $estado ? strtoupper($estado) : null,
                'tipo'   => ($tipo !== null && $tipo !== '') ? (int) $tipo : null,
                'fecha'  => $fecha ?: null,
                'desde'  => $fecha ? null : ($desde ?: null),
                'hasta'  => $fecha ? null : ($hasta ?: null),
                'limit'  => $limit,
            ],
            'total' => $rows->count(),
            'data' => $rows
        ]);
    }
}
