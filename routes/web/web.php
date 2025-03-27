<?php

use App\Http\Controllers\API\V1\Journal\JournalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// generate pdf
Route::get('v1/journal/{journal}/pdf', [JournalController::class, 'generatePDF'])->name('journal.pdf')->middleware(['auth:api', 'verified']);
