<?php


use App\Http\Controllers\API\V1\Journal\JournalController;

use App\Http\Controllers\API\V1\Lulu\LuluPrintJobController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('v1/journal/{journal}/pdf', [JournalController::class, 'generatePDF'])->name('journal.pdf')->middleware(['auth:api', 'verified']);

Route::get('/create-print-job', [LuluPrintJobController::class, 'createPrintJob']);

