<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return redirect()->route('monitoreo');
        }
        return view('welcome');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'user' => ['required','string'],      
            'password' => ['required','string'],
        ]);

        $credentials = [
            'usuario' => $request->input('user'),
            'password' => $request->input('password'),
            'admin' => true,
        ];

        if (!Auth::attempt($credentials)) {
            return back()
                ->withInput($request->only('user'))
                ->withErrors(['user' => 'Credenciales inválidas o no es administrador.']);
        }

        $request->session()->regenerate();
        return redirect()->route('monitoreo');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
