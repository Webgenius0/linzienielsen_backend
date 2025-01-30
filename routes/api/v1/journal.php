<?php

use App\Http\Controllers\API\V1\Journal\JournalController;
use Illuminate\Support\Facades\Route;


Route::controller(JournalController::class)->group(function () {
    Route::post('/store', 'store')->name('store');

});


Route::prefix('/page')->controller(JournalController::class)->group(function () {
    Route::post('/store', 'store')->name('store');
});
