<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');

});

Route::get('/admin', function () {
    return view('Layout_Admin');
});

Route::view('/monitoreo', 'monitoreo');
Route::view('/historial', 'historial');
