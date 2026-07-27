<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['status' => 'online', 'system' => 'Intan-Elyu Tourism API']);
});

Route::get('/up', function () {
    return response('OK', 200);
});
