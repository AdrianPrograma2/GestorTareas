<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\TareaController;
use App\Http\Controllers\ConfigController;

/*
|--------------------------------------------------------------------------
| Rutas de la aplicación Gestor de Tareas - Bunglebuild S.L.
|--------------------------------------------------------------------------
| Se usa el controlador frontal de Laravel (Front Controller Pattern)
| para gestionar todas las peticiones HTTP de la aplicación.
*/

/* Inicio → redirige al login */
Route::get('/', fn() => redirect('login'));

/* -------------------- LOGIN / LOGOUT -------------------- */
Route::get('login',  [UsuarioController::class, 'loginForm']);
Route::post('login', [UsuarioController::class, 'login']);
Route::get('logout', [UsuarioController::class, 'logout']);

/* -------------------- TAREAS -------------------- */
Route::get('tareas',                        [TareaController::class, 'index']);
Route::get('tareas/pendientes',             [TareaController::class, 'pendientes']);
Route::get('tareas/ver/{id}',               [TareaController::class, 'ver']);

Route::get('tareas/crear',                  [TareaController::class, 'crear']);
Route::post('tareas/guardar',               [TareaController::class, 'guardar']);

Route::get('tareas/editar/{id}',            [TareaController::class, 'editar']);
Route::post('tareas/actualizar',            [TareaController::class, 'actualizar']);

Route::get('tareas/completar/{id}',         [TareaController::class, 'completar']);
Route::post('tareas/completar_guardar',     [TareaController::class, 'completarGuardar']);

Route::get('tareas/borrar/{id}',            [TareaController::class, 'borrar']);
Route::post('tareas/borrar_confirmar',      [TareaController::class, 'borrarConfirmar']);

Route::get('tareas/adjunto/{id}',           [TareaController::class, 'descargarAdjunto']);

/* -------------------- CONFIGURACIÓN (solo admin) -------------------- */
Route::get('configuracion',          [ConfigController::class, 'mostrar']);
Route::post('configuracion/guardar', [ConfigController::class, 'guardar']);

/* -------------------- USUARIOS (solo admin) -------------------- */
Route::get('usuarios',                      [UsuarioController::class, 'listar']);
Route::get('usuarios/crear',                [UsuarioController::class, 'crear']);
Route::post('usuarios/guardar',             [UsuarioController::class, 'guardar']);
Route::get('usuarios/editar/{id}',          [UsuarioController::class, 'editar']);
Route::post('usuarios/actualizar',          [UsuarioController::class, 'actualizar']);
Route::get('usuarios/borrar/{id}',          [UsuarioController::class, 'borrar']);
