<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/pdf-merge', [App\Http\Controllers\PdfMergeController::class, 'merge'])->name('pdf.merge');
Route::get('/pdf-preview', [App\Http\Controllers\PdfMergeController::class, 'preview'])->name('pdf.preview');
Route::get('/pdf-download', [App\Http\Controllers\PdfMergeController::class, 'download'])->name('pdf.download');
