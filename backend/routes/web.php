<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'application' => 'MediRDV — API Hôpital Laquintinie',
        'documentation' => '/api',
    ]);
});