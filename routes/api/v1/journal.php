<?php

use App\Http\Controllers\API\V1\Journal\JournalController;
use App\Http\Controllers\API\V1\Journal\JournalPageController;
use Illuminate\Support\Facades\Route;


Route::controller(JournalController::class)->group(function () {
    Route::post('/store', 'store')->name('store');
    Route::post('/archive', 'toggleArchive')->name('archive');

});


Route::prefix('/page')->controller(JournalPageController::class)->group(function () {
    Route::post('/store', 'store')->name('store');
});
