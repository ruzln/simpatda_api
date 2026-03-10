<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\SkprdController;
use App\Http\Controllers\Api\PbbController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// Route::post('/skprd/summary', [SkprdController::class, 'summary']);

Route::prefix('skprd')->group(function () {

    // DETAIL
    Route::post('/office', [SkprdController::class, 'office']);
    Route::post('/self',   [SkprdController::class, 'self']);

    // SUMMARY CARD
    Route::post('/office/summary', [SkprdController::class, 'summaryOffice']);
    Route::post('/self/summary',   [SkprdController::class, 'summarySelf']);
});


Route::post('/pbb/realisasi', [PbbController::class, 'realisasi']);

Route::post('/skprd/combined/summary',  [SkprdController::class, 'combinedSummary']);
Route::post('/skprd/combined',          [SkprdController::class, 'combined']);
Route::post('/skprd/export-wp',         [SkprdController::class, 'exportWp']);





