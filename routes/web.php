<?php

use App\Http\Controllers\FoodSpotController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FoodSpotController::class, 'index'])->name('home');
Route::post('/spots', [FoodSpotController::class, 'store'])
    ->middleware('throttle:5,60')
    ->name('spots.store');
Route::post('/spots/{foodSpot}/vote', [FoodSpotController::class, 'vote'])
    ->middleware('throttle:60,1')
    ->name('spots.vote');
