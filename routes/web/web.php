<?php

use App\Http\Controllers\API\V1\Lulu\LuluPrintJobController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



Route::get('/create-print-job', [LuluPrintJobController::class, 'createPrintJob']);
