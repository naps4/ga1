<?php

use App\Http\Controllers\GaAssetController;
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

// GA Asset Management API Endpoints
Route::prefix('assets')->group(function () {
    Route::get('/master', [GaAssetController::class, 'apiMaster'])->name('api.assets.master');
    Route::get('/plant/{plantCode}', [GaAssetController::class, 'apiPlant'])->name('api.assets.plant');
    Route::get('/lampiran-c/{locationId}', [GaAssetController::class, 'apiLampiranC'])->name('api.assets.lampiran-c');

    // AI Tool-Calling Endpoints
    Route::get('/ai/tools', [GaAssetController::class, 'aiToolDefinitions'])->name('api.assets.ai.tools');
    Route::post('/ai/tool-call', [GaAssetController::class, 'aiToolCall'])->name('api.assets.ai.tool-call');

    Route::post('/', [GaAssetController::class, 'store'])->name('api.assets.store');
    Route::put('/{asset}', [GaAssetController::class, 'update'])->name('api.assets.update');
    Route::post('/{asset}/mutate', [GaAssetController::class, 'mutate'])->name('api.assets.mutate');
    Route::post('/{asset}/condition', [GaAssetController::class, 'updateCondition'])->name('api.assets.condition');
    Route::delete('/{asset}', [GaAssetController::class, 'destroy'])->name('api.assets.destroy');
});
