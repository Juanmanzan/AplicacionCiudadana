<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\EmergenciaController;
use App\Http\Controllers\HistorialController;

Route::get('/', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    Route::view('/monitoreo', 'monitoreo')
        ->name('monitoreo');

    Route::view('/historial', 'historial')
        ->name('historial');

    Route::patch('/emergencias/{id}/estado', [EmergenciaController::class, 'updateEstado']);

    Route::get('/emergencias/activas', [EmergenciaController::class, 'activas'])
    ->name('emergencias.activas');

    Route::get('/historial', [HistorialController::class, 'index'])->name('historial');

    Route::get('/historial/{id}', [HistorialController::class, 'show'])
        ->name('historial.show');

    Route::post('/historial/{id}/atender', [HistorialController::class, 'atender'])
        ->name('historial.atender');

});