<?php

use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
//    return view('welcome');
//});
Route::get('/debug-mail', function () {
    try {
        Mail::raw('Test dari browser', fn($m) => $m->to('jr.pikong@gmail.com')->subject('Browser Test'));
        return 'BERHASIL';
    } catch (\Exception $e) {
        return 'GAGAL: ' . $e->getMessage()
            . '<br>File: ' . $e->getFile()
            . '<br>Line: ' . $e->getLine();
    }
});

Route::get('/privacy', fn () => view('legal.privacy'))->name('privacy');
//Route::get('/terms',   fn () => view('legal.terms'))->name('terms');
