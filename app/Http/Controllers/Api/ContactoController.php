<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contacto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactoController extends Controller
{
    // ✅ Listar mis contactos
    public function index(Request $request)
    {
        $user = $request->user();

        $contactos = Contacto::where('cedula_usuario', $user->cedula)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($contactos);
    }

    // ✅ Crear contacto de emergencia (asociado al usuario logueado)
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'numero_celular' => ['required', 'string', 'max:20'],
            'correo_electronico' => ['nullable', 'email', 'max:150'],
            'parentesco' => ['required', 'string', 'max:50'],
        ]);

        $contacto = Contacto::create([
            'cedula_usuario' => $user->cedula, // 👈 clave: se asigna automáticamente
            'nombres' => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'numero_celular' => $data['numero_celular'],
            'correo_electronico' => $data['correo_electronico'] ?? null,
            'parentesco' => $data['parentesco'],
        ]);

        return response()->json([
            'message' => 'Contacto guardado correctamente.',
            'contacto' => $contacto
        ], 201);
    }

    // ✅ Actualizar un contacto (solo si pertenece al usuario logueado)
    public function update(Request $request, int $id)
    {
        $user = $request->user();

        $contacto = Contacto::where('id_contacto', $id)
            ->where('cedula_usuario', $user->cedula)
            ->firstOrFail();

        $data = $request->validate([
            'nombres' => ['sometimes', 'string', 'max:100'],
            'apellidos' => ['sometimes', 'string', 'max:100'],
            'numero_celular' => ['sometimes', 'string', 'max:20'],
            'correo_electronico' => ['sometimes', 'nullable', 'email', 'max:150'],
            'parentesco' => ['sometimes', 'string', 'max:50'],
        ]);

        // ✅ seguridad: no permitir cambiar cedula_usuario por request
        unset($data['cedula_usuario']);

        $contacto->update($data);

        return response()->json([
            'message' => 'Contacto actualizado.',
            'contacto' => $contacto
        ]);
    }

    // ✅ Eliminar un contacto (solo si pertenece al usuario logueado)
    public function destroy(Request $request, int $id)
    {
        $user = $request->user();

        $contacto = Contacto::where('id_contacto', $id)
            ->where('cedula_usuario', $user->cedula)
            ->firstOrFail();

        $contacto->delete();

        return response()->json(['message' => 'Contacto eliminado.']);
    }
}
