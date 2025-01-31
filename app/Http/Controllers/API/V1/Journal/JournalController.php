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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class JournalController extends Controller
{
    use ApiResponse;
    private JournalService $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    /**
     * Display a list of journals.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $response = $this->journalService->getJournals();
            return $this->success(200, 'Journal List', $response);
        } catch (Exception $e) {
            Log::error('JournalController::index', [$e->getMessage()]);
            return $this->error(500, 'Server Error', $e->getMessage());
        }
    }

    /**
     * Display a list of archived journals.
     *
     * @return JsonResponse
     */
    public function archiveIndex(): JsonResponse
    {
        try {
            $response = $this->journalService->getArchiveJournals();
            return $this->success(200, 'Archive Journal List', $response);
        } catch (Exception $e) {
            Log::error('JournalController::archiveIndex', [$e->getMessage()]);
            return $this->error(500, 'Server Error', $e->getMessage());
        }
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
        } catch (ModelNotFoundException $modelNotFoundException) {
            return $this->error(404, 'Journal Not Found', $modelNotFoundException->getMessage());
        } catch (Exception $e) {
            Log::error('JournalController::toggleArchive', [$e->getMessage()]);
            return $this->error(500, 'Server Error', $e->getMessage());
        }
    }


    /**
     * Search for journals based on a title query.
     *
     * This method handles the search request for journals by title. It fetches the results from
     * the journal service and returns the search results in a JSON response. If no journals are
     * found, a 404 error is returned. In case of a general exception, a 500 error is returned.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing the search parameters.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing the search results or error message.
     *
     * @throws \Exception If an unexpected error occurs during the search process.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $title = $request->query('title');
            $response = $this->journalService->searchJournal($title);
            return $this->success(200, 'Search Result', $response);
        } catch (ModelNotFoundException $modelNotFoundException) {
            return $this->error(404, 'Journal Not Found', $modelNotFoundException->getMessage());
        } catch (Exception $e) {
            Log::error('JournalController::toggleArchive', [$e->getMessage()]);
            return $this->error(500, 'Server Error', $e->getMessage());
        }
    }



    /**
     * Delete a journal entry.
     *
     * This method retrieves the journal ID from the query parameters, attempts
     * to delete the corresponding journal entry, and returns a success response.
     * It handles various exceptions, including:
     * - ModelNotFoundException: If the journal does not exist.
     * - AccessDeniedHttpException: If the user is not authorized to delete the journal.
     * - General Exception: Logs any unexpected errors and returns a server error response.
     *
     * @param  Request  $request  The HTTP request containing the journal ID as a query parameter.
     * @return JsonResponse  A JSON response indicating success or failure.
     *
     * @throws ModelNotFoundException If the journal is not found.
     * @throws AccessDeniedHttpException If the user does not have permission to delete the journal.
     * @throws Exception If any other error occurs.
     */
    public function destroy(Request $request): JsonResponse
    {
        try {
            $journalId = $request->query('journal_id');
            $this->journalService->deleteJournal($journalId);
            return $this->success(200, 'Deleted Successfully');
        } catch (ModelNotFoundException $modelNotFoundException) {
            return $this->error(404, 'Journal Not Found', $modelNotFoundException->getMessage());
        } catch (AccessDeniedHttpException $accessDeniedHttpException) {
            return $this->error(404, 'Access Denied', $accessDeniedHttpException->getMessage());
        } catch (Exception $e) {
            Log::error('JournalController::toggleArchive', [$e->getMessage()]);
            return $this->error(500, 'Server Error', $e->getMessage());
        }
    }
}
