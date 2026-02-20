<?php

use App\Http\Controllers\SecureFileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect('/my/login'));

Route::get('/privacy', fn () => view('legal.privacy'))->name('privacy');
//Route::get('/terms',   fn () => view('legal.terms'))->name('terms');

Route::middleware('auth')->group(function (): void {
    Route::get('/secure-files/documents/{document}', [SecureFileController::class, 'document'])
        ->name('secure-files.documents.download');

    Route::get('/secure-files/payments/{payment}/proof', [SecureFileController::class, 'paymentProof'])
        ->name('secure-files.payments.proof');

    Route::get('/secure-files/applications/{application}/acceptance-letter', [SecureFileController::class, 'acceptanceLetter'])
        ->name('secure-files.applications.acceptance-letter');
});
