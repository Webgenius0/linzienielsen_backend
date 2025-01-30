<?php

namespace App\Http\Controllers\API\V1\Journal;

use App\Http\Controllers\Controller;
use App\Services\API\V1\Journal\JournalService;
use App\Traits\V1\ApiResponse;

class JournalPageController extends Controller
{
    use ApiResponse;
    private JournalService $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }


    public function store()
    {

    }
}
