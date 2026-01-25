<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function updateMe(Request $request)
    {
        /** @var Usuario $user */
        $user = $request->user();

        $data = $request->validate([
            'nombres' => ['sometimes', 'string', 'max:100'],
            'apellidos' => ['sometimes', 'string', 'max:100'],
            'genero' => ['sometimes', Rule::in(['H', 'M'])],
            'edad' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:120'],
            'numero_celular' => ['sometimes', 'nullable', 'string', 'max:20'],
            'correo_electronico' => [
                'sometimes', 'email', 'max:150',
                Rule::unique('usuarios', 'correo_electronico')->ignore($user->cedula, 'cedula')
            ],
            'usuario' => [
                'sometimes', 'string', 'max:60',
                Rule::unique('usuarios', 'usuario')->ignore($user->cedula, 'cedula')
            ],
            'password' => ['sometimes', 'string', 'min:8'],
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        unset($data['admin'], $data['cedula']);

        $user->update($data);

        return response()->json([
            'message' => 'Perfil actualizado.',
            'usuario' => $user->only([
                'cedula','nombres','apellidos','genero','edad',
                'correo_electronico','numero_celular','usuario','admin'
            ])
        ]);
    }

    public function destroyMe(Request $request)
    {
        /** @var Usuario $user */
        $user = $request->user();

        if ($user->emergencias()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar la cuenta: existen reportes (emergencias) asociados.'
            ], 409);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Cuenta eliminada.']);
    }

    public function update(Request $request, string $cedula)
    {
        $usuario = Usuario::where('cedula', $cedula)->firstOrFail();

        $data = $request->validate([
            'nombres' => ['sometimes', 'string', 'max:100'],
            'apellidos' => ['sometimes', 'string', 'max:100'],
            'genero' => ['sometimes', Rule::in(['H', 'M'])],
            'edad' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:120'],
            'numero_celular' => ['sometimes', 'nullable', 'string', 'max:20'],

            'correo_electronico' => [
                'sometimes', 'email', 'max:150',
                Rule::unique('usuarios', 'correo_electronico')->ignore($usuario->cedula, 'cedula')
            ],
            'usuario' => [
                'sometimes', 'string', 'max:60',
                Rule::unique('usuarios', 'usuario')->ignore($usuario->cedula, 'cedula')
            ],

            'password' => ['sometimes', 'string', 'min:8'],

            // admin puede cambiar admin si lo necesitas
            'admin' => ['sometimes', 'boolean'],
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // No cambiar cedula
        unset($data['cedula']);

        $usuario->update($data);

        return response()->json([
            'message' => 'Usuario actualizado (admin).',
            'usuario' => $usuario->only([
                'cedula','nombres','apellidos','genero','edad',
                'correo_electronico','numero_celular','usuario','admin'
            ])
        ]);
    }
    public function destroy(string $cedula)
    {
        $usuario = Usuario::where('cedula', $cedula)->firstOrFail();

        if ($usuario->emergencias()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar: existen reportes (emergencias) asociados.'
            ], 409);
        }

        $usuario->tokens()->delete();
        $usuario->delete();

        return response()->json(['message' => 'Usuario eliminado (admin).']);
    }
}
