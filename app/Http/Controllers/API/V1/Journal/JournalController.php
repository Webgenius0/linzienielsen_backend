<?php

namespace App\Http\Controllers\API\V1\Journal;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\Journal\CreateJournalRequest;
use App\Http\Requests\API\V1\Journal\JournalArchiveRequest;
use App\Models\Journal;
use App\Services\API\V1\Journal\JournalService;
use App\Traits\V1\ApiResponse;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class JournalController extends Controller
{
    use ApiResponse;
    private JournalService $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }


    /**
     * Handle the creation of a new journal.
     *
     * @param CreateJournalRequest $createJournalRequest The request containing the validated data for the new journal.
     * @return \Illuminate\Http\JsonResponse A JSON response with the result of the journal creation process.
     * @throws \Exception If an error occurs during the creation process, a server error response will be returned.
     */
    public function store(CreateJournalRequest $createJournalRequest): JsonResponse
    {
        try {
            $validatedData = $createJournalRequest->validated();
            $response = $this->journalService->createJournal($validatedData);
            return $this->success(200, 'Journal Created Successfully', $response);
        } catch (Exception $e) {
            Log::error('JournalController::store', [$e->getMessage()]);
            return $this->error(500, 'Server Error', $e->getMessage());
        }
    }


    public function toggleArchive(JournalArchiveRequest $journalArchiveRequest): JsonResponse
    {
        try {
            $validatedData = $journalArchiveRequest->validated();
            $this->journalService->toggleArchive($validatedData);
            return $this->success(200, 'Arcive Status Changed');
        } catch(ModelNotFoundException $modelNotFoundException) {
            return $this->error(404, 'Journal Not Found', $modelNotFoundException->getMessage());
        }catch (Exception $e) {
            Log::error('JournalController::toggleArchive', [$e->getMessage()]);
            return $this->error(500, 'Server Error', $e->getMessage());
        }
    }
}
