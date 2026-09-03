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

Route::middleware(['auth:sanctum', 'active', 'clinic.context'])->group(function () {
    Route::get('/patients', [PatientApiController::class, 'index'])
        ->middleware('can:viewAny,App\Models\Patient');
    Route::get('/patients/{patient}', [PatientApiController::class, 'show'])
        ->middleware('can:view,patient');
    Route::post('/patients', [PatientApiController::class, 'store'])
        ->middleware('can:create,App\Models\Patient');
    Route::put('/patients/{patient}', [PatientApiController::class, 'update'])
        ->middleware('can:update,patient');
    Route::delete('/patients/{patient}', [PatientApiController::class, 'destroy'])
        ->middleware('can:delete,patient');
});
