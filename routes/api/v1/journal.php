<?php

use App\Http\Controllers\API\V1\Journal\JournalController;
use App\Http\Controllers\API\V1\Journal\JournalPageController;
use Illuminate\Support\Facades\Route;


Route::controller(JournalController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/archive', 'archiveIndex')->name('archive.index');
    Route::get('/', 'index')->name('index');
    Route::post('/store', 'store')->name('store');
    Route::post('/archive', 'toggleArchive')->name('archive');
    Route::get('/search', 'search')->name('search');
    Route::delete('/destroy', 'destroy')->name('destroy');
});


Route::prefix('/page')->name('page.')->controller(JournalPageController::class)->group(function () {
    Route::get('/', 'index')->name('page');
    Route::post('/store', 'store')->name('store');
    Route::get('/show', 'show')->name('show');
});
