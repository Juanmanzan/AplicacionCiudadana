<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Emergencia;

class HistorialController extends Controller
{
    public function index(Request $request)
    {
        // ✅ nuevo: si viene desde el mapa
        $emergenciaId = $request->query('emergencia'); // ?emergencia=24

        // 1) Leer filtros
        $genero     = $request->query('genero');       // M | F | null
        $fechaDesde = $request->query('fecha_desde');  // YYYY-MM-DD
        $fechaHasta = $request->query('fecha_hasta');  // YYYY-MM-DD
        $horaDesde  = $request->query('hora_desde');   // HH:MM
        $estado     = $request->query('estado');       // CONFIRMADA|ATENDIDA|FALSA_ALARMA|null
        $tipos      = $request->query('tipos', []);    // array
        $perPage    = (int) $request->query('per_page', 10);

        if (!is_array($tipos)) $tipos = [$tipos];
        $perPage = max(5, min($perPage, 50));

        // ✅ Catálogo fijo (evita error de columna "nombre")
        $tiposCatalogo = collect([
            (object)['id' => 1, 'nombre' => 'Emergencia médica'],
            (object)['id' => 2, 'nombre' => 'Incendio'],
            (object)['id' => 3, 'nombre' => 'Asalto'],
            (object)['id' => 4, 'nombre' => 'Siniestro de tránsito'],
        ]);

        // para el blade (labels rápidos)
        $tipoLabels = [
            1 => 'Emergencia médica',
            2 => 'Incendio',
            3 => 'Asalto',
            4 => 'Siniestro de tránsito',
        ];

        // 2) Query base (admin: todas)
        $q = DB::table('emergencia as e')
            ->leftJoin('usuarios as u', 'u.cedula', '=', 'e.cedula_usuario')
            ->selectRaw('
                e.id_emergencia,
                e.id_tipo_emergencia,
                e.descripcion,
                e.estado,
                e.fecha_hora,
                ST_Y(e.ubicacion::geometry) AS lat,
                ST_X(e.ubicacion::geometry) AS lng,
                u.cedula as cedula_usuario,
                u.nombres,
                u.apellidos,
                u.genero
            ')
            ->orderByDesc('e.fecha_hora');

        // ✅ 2.1) Si viene emergencia=ID, filtra SOLO ese registro
        $openId = null;
        if ($emergenciaId !== null && $emergenciaId !== '') {
            $openId = (int)$emergenciaId;
            $q->where('e.id_emergencia', $openId);
        }

        // 3) Filtros normales
        if ($genero === 'M' || $genero === 'F') {
            $q->where('u.genero', $genero);
        }

        $tiposFiltrados = array_values(array_filter(array_map('intval', $tipos), fn($v) => $v > 0));
        if (count($tiposFiltrados) > 0) {
            $q->whereIn('e.id_tipo_emergencia', $tiposFiltrados);
        }

        if ($fechaDesde) $q->whereRaw('DATE(e.fecha_hora) >= ?', [$fechaDesde]);
        if ($fechaHasta) $q->whereRaw('DATE(e.fecha_hora) <= ?', [$fechaHasta]);

        if ($horaDesde) {
            $q->whereRaw("to_char(e.fecha_hora, 'HH24:MI') >= ?", [$horaDesde]);
        }

        // ✅ filtro por estado (solo 3 estados válidos)
        if (in_array($estado, ['CONFIRMADA', 'ATENDIDA', 'FALSA_ALARMA'], true)) {
            $q->where('e.estado', $estado);
        }

        $rows = $q->paginate($perPage)->withQueryString();

        return view('historial', [
            'rows' => $rows,
            'tiposCatalogo' => $tiposCatalogo,
            'tipoLabels' => $tipoLabels,
            'openId' => $openId, // ✅ para auto-abrir modal
            'filters' => [
                'genero' => $genero,
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
                'hora_desde' => $horaDesde,
                'estado' => $estado,
                'tipos' => $tiposFiltrados,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function atender(Request $request, $id)
    {
        $em = Emergencia::findOrFail($id);

        // ✅ solo permitir si está CONFIRMADA
        if ($em->estado !== Emergencia::ESTADO_CONFIRMADA && $em->estado !== 'CONFIRMADA') {
            return redirect()
                ->back()
                ->with('error', 'Solo se puede confirmar atención cuando la emergencia está en estado CONFIRMADA.');
        }

        $em->estado = Emergencia::ESTADO_ATENDIDA; // ATENDIDA
        $em->save();

        // ✅ vuelve al historial manteniendo filtros (si viniste con querystring)
        return redirect()
            ->route('historial', $request->query())
            ->with('success', 'Emergencia marcada como ATENDIDA correctamente.');
    }
}
