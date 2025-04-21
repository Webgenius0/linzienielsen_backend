<?php

<<<<<<< HEAD
use App\Http\Controllers\API\V1\Journal\JournalController;
=======
use App\Http\Controllers\API\V1\Lulu\LuluPrintJobController;
>>>>>>> lulu
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

<<<<<<< HEAD
// generate pdf
Route::get('v1/journal/{journal}/pdf', [JournalController::class, 'generatePDF'])->name('journal.pdf')->middleware(['auth:api', 'verified']);
=======


Route::get('/create-print-job', [LuluPrintJobController::class, 'createPrintJob']);
>>>>>>> lulu
