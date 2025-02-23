<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GroqController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/api/groq/query', [GroqController::class, 'query']);
Route::post('groq/query', [GroqController::class, 'query'])->name('groq.query');