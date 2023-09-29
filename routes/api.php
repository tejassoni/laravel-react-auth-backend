<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// authentications routes
Route::post('/login',[\App\Http\Controllers\Api\AuthController::class,'login']);
Route::post('/register',[\App\Http\Controllers\Api\AuthController::class,'register']);

// protected routes with sanctum
Route::group(['middleware' => ['auth:sanctum']],function () {
    Route::post('/logout',[\App\Http\Controllers\Api\AuthController::class,'logout']);
    Route::get("/refresh", [\App\Http\Controllers\Api\AuthController::class, 'refresh']);
    Route::get("/profile", [\App\Http\Controllers\Api\AuthController::class, 'profile']);
    // Update Existing User password basis on Old Password and New Password
    Route::post('/change-password',[\App\Http\Controllers\Api\AuthController::class,'updatePassword']);
});