<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompareController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/compare', function () {
    return view('compare');
});

Route::get('/schema-diff', [CompareController::class, 'schemaDiff']);
Route::get('/data-diff', [CompareController::class, 'dataDiff']);

