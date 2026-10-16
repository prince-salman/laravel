<?php

use App\Http\Controllers\Api\NpcController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route RESTful API untuk fitur NPC (index, store, show, update, destroy)
Route::apiResource('npcs', NpcController::class);

// Route POST untuk update data multipart (karena browser HTML form upload gambar lebih mudah via POST)
Route::post('npcs/{npc}', [NpcController::class, 'update']);
