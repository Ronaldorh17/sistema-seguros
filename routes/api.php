<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RreeTestController;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/rree/login-test', [RreeTestController::class, 'login']);
Route::post('/rree/user-test', [RreeTestController::class, 'user']);
Route::post(
    '/rree/sync-test',
    [RreeTestController::class, 'syncUser']
);