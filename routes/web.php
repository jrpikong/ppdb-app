<?php

use App\Http\Controllers\SecureFileController;
use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
//    return view('welcome');
//});
Route::get('/privacy', fn () => view('legal.privacy'))->name('privacy');
//Route::get('/terms',   fn () => view('legal.terms'))->name('terms');

Route::middleware('auth')->group(function (): void {
    Route::get('/secure-files/documents/{document}', [SecureFileController::class, 'document'])
        ->name('secure-files.documents.download');

    Route::get('/secure-files/payments/{payment}/proof', [SecureFileController::class, 'paymentProof'])
        ->name('secure-files.payments.proof');
});
