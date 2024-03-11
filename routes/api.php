<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProgramController;

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

//program
Route::get('programs', [ProgramController::class,'index']);
Route::post('program', [ProgramController::class,'store']);
Route::put('program/edit/{id}', [ProgramController::class,'edit']);
Route::delete('program/delete/{id}', [ProgramController::class,'delete']);