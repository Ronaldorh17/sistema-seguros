<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RreeTestController;
use App\Http\Controllers\AuthController;



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

});