<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PatientApiController;

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

Route::middleware('auth:sanctum')->group(function () {
    // Rutas de la API de pacientes
    Route::get('/patients', [PatientApiController::class, 'index']);
    Route::get('/patients/{patient}', [PatientApiController::class, 'show']);
    Route::post('/patients', [PatientApiController::class, 'store']);
    Route::put('/patients/{patient}', [PatientApiController::class, 'update']);
    Route::delete('/patients/{patient}', [PatientApiController::class, 'destroy']);
});
