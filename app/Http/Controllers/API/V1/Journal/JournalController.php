<?php

namespace App\Http\Controllers\API\V1\Journal;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\Journal\CreateJournalRequest;
use App\Services\API\V1\Journal\JournalService;
use App\Traits\V1\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JournalController extends Controller
{
    use ApiResponse;
    private JournalService $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function store(CreateJournalRequest $createJournalRequest)
    {
        try {
            $validatedData = $createJournalRequest->validated();
            $this->journalService->createJournal($validatedData);
        } catch (Exception $e) {
            Log::error('JournalController::store', [$e->getMessage()]);
            return $this->error(500, 'Server Error', $e->getMessage());
        }
    }
}
