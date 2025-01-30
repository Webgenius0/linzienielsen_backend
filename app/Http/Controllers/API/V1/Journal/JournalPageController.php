<?php

namespace App\Http\Controllers\API\V1\Journal;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\Journal\CreateJournalPageRequest;
use App\Services\API\V1\Journal\JournalService;
use App\Traits\V1\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class JournalPageController extends Controller
{
    use ApiResponse;
    private JournalService $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }


    /**
     * Handle the creation of a new journal page.
     *
     * @param CreateJournalPageRequest $createJournalPageRequest The request containing the validated data for the new journal page.
     * @return JsonResponse A success response with the created journal page details.
     * @throws \Exception If an error occurs during the creation process, a server error response will be returned.
     */
    public function store(CreateJournalPageRequest $createJournalPageRequest): JsonResponse
    {
        try {
            $validatedData = $createJournalPageRequest->validated();
            $response = $this->journalService->createJournalPage($validatedData);
            return $this->success(200, 'Journal Page Created Successfully', $response);
        } catch (Exception $e) {
            Log::error('JournalService::processImagesInHtml', [$e->getMessage()]);
            return $this->error(500, 'Server Error', $e->getMessage());
        }
    }
}
