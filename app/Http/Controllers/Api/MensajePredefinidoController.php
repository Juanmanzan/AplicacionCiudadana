<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MensajePredefinido;
use Illuminate\Http\Request;

class MensajePredefinidoController extends Controller
{
    public function index(Request $request)
    {
        $u = $request->user();

        return response()->json(
            MensajePredefinido::where('cedula_usuario', $u->cedula)
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $u = $request->user();

        $data = $request->validate([
            'id_tipo_emergencia' => ['required', 'integer', 'exists:tipo_emergencia,id_tipo_emergencia'],
            'mensaje' => ['required', 'string', 'max:1000'],
            'combinacion_botones' => ['nullable', 'string', 'max:50'],
        ]);

        // ✅ Evitar duplicado según tu constraint UNIQUE
        $exists = MensajePredefinido::where('cedula_usuario', $u->cedula)
            ->where('id_tipo_emergencia', $data['id_tipo_emergencia'])
            ->where('combinacion_botones', $data['combinacion_botones'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Ya existe un mensaje con esa combinación para ese tipo de emergencia.'
            ], 409);
        }

        $registro = MensajePredefinido::create([
            'cedula_usuario' => $u->cedula,
            'id_tipo_emergencia' => $data['id_tipo_emergencia'],
            'mensaje' => $data['mensaje'],
            'combinacion_botones' => $data['combinacion_botones'] ?? null,
        ]);

        return response()->json([
            'message' => 'Mensaje predeterminado guardado.',
            'mensaje_predefinido' => $registro
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $u = $request->user();

        $registro = MensajePredefinido::where('id_mensaje_predefinido', $id)
            ->where('cedula_usuario', $u->cedula)
            ->firstOrFail();

        $data = $request->validate([
            'id_tipo_emergencia' => ['sometimes', 'integer', 'exists:tipo_emergencia,id_tipo_emergencia'],
            'mensaje' => ['sometimes', 'string', 'max:1000'],
            'combinacion_botones' => ['sometimes', 'nullable', 'string', 'max:50'],
        ]);

        unset($data['cedula_usuario']);

        $registro->update($data);

        return response()->json([
            'message' => 'Mensaje predeterminado actualizado.',
            'mensaje_predefinido' => $registro
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $u = $request->user();

        $registro = MensajePredefinido::where('id_mensaje_predefinido', $id)
            ->where('cedula_usuario', $u->cedula)
            ->firstOrFail();

        $registro->delete();

        return response()->json(['message' => 'Mensaje predeterminado eliminado.']);
    }
}
