<?php

namespace App\Http\Controllers\API\V1\Journal;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\Journal\CreateJournalPageRequest;
use App\Services\API\V1\Journal\JournalService;
use App\Traits\V1\ApiResponse;
use Exception;
use Illuminate\Support\Facades\Log;

class JournalPageController extends Controller
{
    use ApiResponse;
    private JournalService $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }


    public function store(CreateJournalPageRequest $createJournalPageRequest)
    {
        try {
            $validatedData = $createJournalPageRequest->validated();
            $response = $this->journalService->createJournalPage($validatedData);
            return $this->success(200, 'Journal Page Created Successfully', $response);
        }catch(Exception $e) {
            Log::error('JournalService::processImagesInHtml', [$e->getMessage()]);
            $this->error(500, 'Server Error', $e->getMessage());
        }
    }
}
