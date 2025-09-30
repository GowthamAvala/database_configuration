<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompareController;



Route::get('/', function () {
    return view('compare');
});

Route::get('/schema-diff', [CompareController::class, 'schemaDiff']);
Route::get('/data-diff', [CompareController::class, 'dataDiff']);

Route::get('/compare-result', [CompareController::class, 'showResult'])->name('compare.result');
Route::get('/compare-result/pdf', [CompareController::class, 'downloadPdf'])->name('compare.download.pdf');
Route::get('/compare-result/excel', [CompareController::class, 'downloadExcel'])->name('compare.download.excel');
Route::get('/compare/download/sql', [CompareController::class, 'downloadSql'])->name('compare.download.sql');