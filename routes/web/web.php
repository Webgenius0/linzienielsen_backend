<?php

use App\Http\Controllers\API\V1\Journal\JournalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/journal/{journal}/pdf', [JournalController::class, 'generatePDF'])->name('journal.pdf');
