<?php

use App\Http\Controllers\Dashboard\SkprdDashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SkprdController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('skprd')->group(function () {
    Route::view('/self', 'skprd.self')->name('skprd.self');
    Route::view('/office', 'skprd.office')->name('skprd.office');
});

Route::get('/pbb', function () {
    return view('pbb.index');})->name('pbb.index');

Route::get('/skprd/combined', function () {
    return view('skprd.combined');
})->name('skprd.combined');

Route::get('/skprd/daftar-wp', [SkprdController::class, 'daftarWp'])->name('skprd.daftar-wp');
