<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;
use App\Models\Emergencia;
use App\Events\EmergenciaCreada;
use App\Events\EmergenciaActualizada;

class EmergenciaController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'cedula_usuario'      => ['nullable','string','max:10'],
            'id_tipo_emergencia'  => ['required','integer'],
            'descripcion'         => ['nullable','string','max:280'],
            'lat'                 => ['required','numeric'],
            'lng'                 => ['required','numeric'],
        ]);

        
        $cedula = $data['cedula_usuario'] ?? null;

        
        if (!$cedula && auth()->check()) {
            $cedula = auth()->user()->cedula;
        }

        
        if (!$cedula) {
            return response()->json([
                'ok' => false,
                'message' => 'cedula_usuario es requerida (o autentícate).'
            ], 422);
        }

        
        if (!Usuario::where('cedula', $cedula)->exists()) {
            return response()->json([
                'ok' => false,
                'message' => 'La cédula no existe en usuarios.',
                'cedula_usuario' => $cedula,
            ], 422);
        }

       $emergencia = Emergencia::create([
            'cedula_usuario'     => $cedula,
            'id_tipo_emergencia' => $data['id_tipo_emergencia'],
            'descripcion'        => $data['descripcion'] ?? null,
            'fecha_hora'         => now(),

            // ✅ nuevo flujo:
            'estado'             => Emergencia::ESTADO_EN_VERIFICACION,
            'cancelable_hasta'   => now()->addMinute(),
            'confirmada_en'      => null,

            'ubicacion'          => DB::raw("ST_SetSRID(ST_MakePoint({$data['lng']}, {$data['lat']}), 4326)"),
        ]);

        $emergencia->refresh();

        broadcast(new EmergenciaCreada($emergencia));

        return response()->json([
            'ok' => true,
            'id_emergencia' => $emergencia->id_emergencia,
            'estado' => $emergencia->estado,
            'cancelable_hasta' => optional($emergencia->cancelable_hasta)->toISOString(),
        ], 201);
    }

    public function updateEstado(Request $request, $id)
    {
        $data = $request->validate([
            'estado' => 'required|string|in:EN_VERIFICACION,CONFIRMADA,ATENDIDA,FALSA_ALARMA',
        ]);

        $emergencia = Emergencia::findOrFail($id);
        $emergencia->estado = $data['estado'];
        $emergencia->save();

        broadcast(new EmergenciaActualizada($emergencia));

        return response()->json(['ok' => true]);
    }

   public function activas()
    {
        $rows = DB::table('emergencia as e')
            ->join('usuarios as u', 'u.cedula', '=', 'e.cedula_usuario')
            ->selectRaw('
                e.id_emergencia,
                e.id_tipo_emergencia,
                e.descripcion,
                e.estado,
                ST_Y(e.ubicacion::geometry) AS lat,
                ST_X(e.ubicacion::geometry) AS lng,
                e.fecha_hora,
                e.cancelable_hasta,

                u.cedula as cedula_usuario,
                u.nombres,
                u.apellidos,
                u.genero,
                u.edad
            ')
            ->whereIn('e.estado', [Emergencia::ESTADO_EN_VERIFICACION, Emergencia::ESTADO_CONFIRMADA])
            ->orderByDesc('e.fecha_hora')
            ->get();

        return response()->json($rows);
    }

    // cancelar 

    public function cancelar(Request $request, $id)
    {
        $emergencia = Emergencia::findOrFail($id);

        // Solo se puede cancelar si aún está en verificación
        if ($emergencia->estado !== Emergencia::ESTADO_EN_VERIFICACION) {
            return response()->json([
                'ok' => false,
                'message' => 'La emergencia ya no es cancelable.',
            ], 409);
        }

        // Validar ventana de tiempo
        if (!$emergencia->cancelable_hasta || now()->greaterThan($emergencia->cancelable_hasta)) {
            return response()->json([
                'ok' => false,
                'message' => 'Tiempo de cancelación expirado.',
            ], 409);
        }

        // (Opcional recomendado) Validar que solo el mismo usuario cancele
        // Si tienes auth:
        if (auth()->check() && auth()->user()->cedula !== $emergencia->cedula_usuario) {
            return response()->json([
                'ok' => false,
                'message' => 'No autorizado para cancelar esta emergencia.',
            ], 403);
        }

        $emergencia->estado = Emergencia::ESTADO_FALSA_ALARMA;
        $emergencia->save();

        broadcast(new EmergenciaActualizada($emergencia));

        return response()->json([
            'ok' => true,
            'estado' => $emergencia->estado,
        ]);
    }

    
}
