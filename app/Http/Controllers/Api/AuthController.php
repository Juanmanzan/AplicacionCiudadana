<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'cedula' => ['required', 'string', 'max:10', 'unique:usuarios,cedula'],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'genero' => ['required', Rule::in(['H', 'M'])],

            'correo_electronico' => ['required', 'email', 'max:150', 'unique:usuarios,correo_electronico'],
            'numero_celular' => ['nullable', 'string', 'max:20'],

            'usuario' => ['required', 'string', 'max:60', 'unique:usuarios,usuario'],
            'password' => ['required', 'string', 'min:8'],

            'edad' => ['nullable', 'integer', 'min:0', 'max:120'],
        ]);

        $usuario = Usuario::create([
            'cedula' => $data['cedula'],
            'nombres' => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'genero' => $data['genero'],
            'correo_electronico' => $data['correo_electronico'],
            'numero_celular' => $data['numero_celular'] ?? null,
            'usuario' => $data['usuario'],
            'password' => Hash::make($data['password']),
            'edad' => $data['edad'] ?? null,
            'admin' => false, 
        ]);

        $token = $usuario->createToken('mobile')->plainTextToken;

        return response()->json([
            'message' => 'Registro exitoso.',
            'token' => $token,
            'usuario' => [
                'cedula' => $usuario->cedula,
                'nombres' => $usuario->nombres,
                'apellidos' => $usuario->apellidos,
                'genero' => $usuario->genero,
                'correo_electronico' => $usuario->correo_electronico,
                'numero_celular' => $usuario->numero_celular,
                'usuario' => $usuario->usuario,
                'edad' => $usuario->edad,
                'admin' => $usuario->admin,
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => ['required', 'string'],  
            'password' => ['required', 'string'],
        ]);

        $usuario = Usuario::where('usuario', $data['login'])
            ->orWhere('correo_electronico', $data['login'])
            ->first();

        if (!$usuario || !Hash::check($data['password'], $usuario->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas.'
            ], 401);
        }


        $token = $usuario->createToken('mobile')->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso.',
            'token' => $token,
            'usuario' => [
                'cedula' => $usuario->cedula,
                'nombres' => $usuario->nombres,
                'apellidos' => $usuario->apellidos,
                'genero' => $usuario->genero,
                'edad' => $usuario->edad,
                'admin' => $usuario->admin,
            ],
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Sesión cerrada (token actual revocado).']);
        }

        $user->tokens()->delete();
        return response()->json(['message' => 'Sesión cerrada (todos los tokens revocados).']);
    }
}
