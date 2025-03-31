<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NoteController;
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


// Login
Route::post("/login",[AuthController::class,"login"]);


// Signup
Route::post("/signup",[AuthController::class,"sign_up"]);


Route::middleware('auth:api')->group(function() {

    // Retrive data depend on tenant
    Route::get('/notes', [NoteController::class, 'getNotes']);

});


