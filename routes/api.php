<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RreeTestController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PolizaController;
use App\Http\Controllers\DevAuthController;
use App\Http\Controllers\DocumentoPolizaController;
use App\Http\Controllers\ActivosFijosController;
use App\Http\Controllers\AdministracionActivosFijosController;
use App\Http\Controllers\UserController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/rree/login-test', [RreeTestController::class, 'login']);
Route::post('/rree/user-test', [RreeTestController::class, 'user']);
Route::post(
    '/rree/sync-test',
    [RreeTestController::class, 'syncUser']
);

Route::post('/auth/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {

Route::get('/auth/me', [AuthController::class, 'me']);

    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get(
        '/administracion/activos-fijos/usuarios',
        [AdministracionActivosFijosController::class, 'index']
    );

    Route::put(
        '/administracion/usuarios/{user}/activos-fijos',
        [AdministracionActivosFijosController::class, 'update']
    );

    Route::get('/polizas', [PolizaController::class, 'index']);
    Route::post('/polizas', [PolizaController::class, 'store']);
    Route::post(
    '/polizas/{poliza}/enviar-revision',
    [PolizaController::class, 'enviarRevision']
);
    Route::post(
    '/polizas/{poliza}/observar',
    [PolizaController::class, 'observar']
);

Route::post(
    '/polizas/{poliza}/validar',
    [PolizaController::class, 'validar']
);

Route::post(
    '/polizas/{poliza}/bloquear',
    [PolizaController::class, 'bloquear']
);
Route::get(
    '/polizas/{poliza}',
    [PolizaController::class, 'show']
);
Route::put(
    '/polizas/{poliza}',
    [PolizaController::class, 'update']
);

Route::post(
    '/polizas/{poliza}/documentos',
    [DocumentoPolizaController::class, 'store']
);

Route::get(
    '/polizas/{poliza}/documentos',
    [DocumentoPolizaController::class, 'index']
);

Route::get(
    '/documentos/{documento}/descargar',
    [DocumentoPolizaController::class, 'download']
);

Route::delete(
    '/documentos/{documento}',
    [DocumentoPolizaController::class, 'destroy']
);

Route::get(
    '/activos-fijos/dashboard',
    [ActivosFijosController::class, 'dashboard']
);

Route::get(
    '/activos-fijos/polizas-pendientes',
    [ActivosFijosController::class, 'polizasPendientes']
);

Route::get(
    '/activos-fijos/polizas/{poliza}/revision',
    [ActivosFijosController::class, 'detalleRevision']
);

Route::get(
    '/mis-polizas',
    [PolizaController::class, 'misPolizas']
);

Route::get(
    '/mis-polizas/resumen',
    [PolizaController::class, 'resumenMisPolizas']
);

Route::get(
    '/mis-polizas/{poliza}',
    [PolizaController::class, 'detalleMiPoliza']
);

Route::get(
    '/activos-fijos/polizas',
    [ActivosFijosController::class, 'polizas']
);

Route::get(
    '/activos-fijos/polizas/{poliza}',
    [ActivosFijosController::class, 'detallePoliza']
);

Route::get(
    '/administracion/usuarios',
    [UserController::class, 'index']
);

Route::get(
    '/administracion/usuarios/{user}',
    [UserController::class, 'show']
);

Route::put(
    '/administracion/usuarios/{user}/rol',
    [UserController::class, 'updateRole']
);


});

Route::post(
    '/dev/login',
    [DevAuthController::class, 'login']
);
