<?php

use App\Http\Controllers\API\V1\Profile\UserController;
use Illuminate\Support\Facades\Route;


Route::controller(UserController::class)->group(function () {
    Route::patch('/show', 'show')->name('show');
    Route::patch('/update', 'update')->name('update');
});
