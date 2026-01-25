<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmergenciaController;
use App\Http\Controllers\Api\EmergenciaApiController;


use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContactoController;
use App\Http\Controllers\Api\MensajePredefinidoController;
use App\Http\Controllers\Api\UsuarioController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);




Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Contactos de emergencia del usuario logueado
    Route::get('/contactos', [ContactoController::class, 'index']);
    Route::post('/contactos', [ContactoController::class, 'store']);
    Route::put('/contactos/{id}', [ContactoController::class, 'update']);
    Route::delete('/contactos/{id}', [ContactoController::class, 'destroy']);

    // Mensajes predefinidos del usuario logueado
    Route::get('/mensajes-predefinidos', [MensajePredefinidoController::class, 'index']);
    Route::post('/mensajes-predefinidos', [MensajePredefinidoController::class, 'store']);
    Route::put('/mensajes-predefinidos/{id}', [MensajePredefinidoController::class, 'update']);
    Route::delete('/mensajes-predefinidos/{id}', [MensajePredefinidoController::class, 'destroy']);

    // Usuario logueado (perfil propio)
    Route::put('/usuarios/me', [UsuarioController::class, 'updateMe']);
    Route::delete('/usuarios/me', [UsuarioController::class, 'destroyMe']);

    // Solo admin (gestión de cualquier usuario)
    Route::middleware('admin.only')->group(function () {
        Route::put('/usuarios/{cedula}', [UsuarioController::class, 'update']);
        Route::delete('/usuarios/{cedula}', [UsuarioController::class, 'destroy']);
    });

    Route::post('/emergencias', [EmergenciaController::class, 'store'])
    ->name('api.emergencias.store');

    Route::get('/emergencias/{id}', [EmergenciaApiController::class, 'show']); 

    Route::get('/usuarios/{cedula}/emergencias', [EmergenciaApiController::class, 'historialPorCedula']); 

    Route::post('/emergencias/{id}/cancelar', [EmergenciaController::class, 'cancelar'])
    ->name('api.emergencias.cancelar');

});
