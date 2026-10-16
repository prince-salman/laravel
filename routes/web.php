<?php

use Illuminate\Support\Facades\Route;

// Halaman utama aplikasi mengarah ke tampilan CRUD NPC
Route::get('/', function () {
    return view('npcs');
});
