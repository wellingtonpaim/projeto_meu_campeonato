<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChampionshipController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/championships', [ChampionshipController::class, 'store']);
Route::post('/championships/{id}/enroll', [ChampionshipController::class, 'enroll']);
