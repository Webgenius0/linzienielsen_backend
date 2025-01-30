<?php

namespace App\Repositories\API\V1\Journal;

use App\Models\Image;
use App\Models\Journal;
use App\Models\JournalNotification;
use App\Models\JournalPage;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class JournalRepository implements JournalRepositoryInterface
{
    /**
     * List public journals for a specific user.
     *
     * This method retrieves a list of journals that are not archived for a given user,
     * including the first page of each journal ordered by ID.
     *
     * @param int $userId The ID of the user whose public journals are to be retrieved.
     * @throws Exception If an error occurs during the retrieval process.
     */
    public function listUserPublicJournals($userId)
    {
        try {
            $journals = Journal::select('id', 'title', 'created_at')
                ->whereUserId($userId)
                ->whereArchive(false)
                ->with(['JournalPages' => function ($query) {
                    $query->orderBy('id', 'asc')->limit(1);
                }])
                ->orderBy('id', 'desc')
                ->get();

            $journals->makeVisible('created_at');

            return $journals;
        } catch (Exception $e) {
            Log::error('JournalRepository::listpublicUserJournals', [$e->getMessage()]);
            throw $e;
        }
    }

    /**
     * List archived journals for a specific user.
     *
     * This method retrieves a list of archived journals for a given user,
     * including the first page of each journal ordered by ID.
     *
     * @param int $userId The ID of the user whose archived journals are to be retrieved.
     * @throws Exception If an error occurs during the retrieval process.
     */
    public function listUserArchivedJournals($userId)
    {
        try {
            $journals = Journal::select('id', 'title', 'created_at')
                ->whereUserId($userId)
                ->whereArchive(true)
                ->with(['JournalPages' => function ($query) {
                    $query->orderBy('id', 'asc')->limit(1);
                }])
                ->orderBy('id', 'desc')
                ->get();

            $journals->makeVisible('created_at');
            return $journals;
        } catch (Exception $e) {
            Log::error('JournalRepository::listpublicUserJournals', [$e->getMessage()]);
            throw $e;
        }
    }


    /**
     * List pages of a specific journal.
     *
     * This method retrieves the journal along with its pages, ordered by ID in descending order.
     *
     * @param int $journalId The ID of the journal whose pages are to be retrieved.
     * @throws Exception If an error occurs during the retrieval process.
     */
    public function listJournalPages($journalId)
    {
        try {
            $journals = Journal::select('id', 'title', 'created_at')
                ->whereId($journalId)
                ->with(['JournalPages' => function ($query) {
                    $query->orderBy('id', 'desc');
                }])
                ->get();

            $journals->makeVisible('created_at');

            return $journals;
        } catch (ModelNotFoundException $modelNotFoundException) {
            throw $modelNotFoundException;
        } catch (Exception $e) {
            Log::error('JournalRepository::listJournalPage', [$e->getMessage()]);
            throw $e;
        }
    }


    /**
     * Show a specific journal page.
     *
     * This method retrieves a single journal page by its ID.
     *
     * @param int $journalPageId The ID of the journal page to be retrieved.
     * @throws Exception If an error occurs during the retrieval process.
     */
    public function showJournalPage($journalPageId)
    {
        try {
            $page =  JournalPage::findOrFail($journalPageId);
            $page->makeVisible('created_at');

            return $page;
        } catch (ModelNotFoundException $modelNotFoundException) {
            throw $modelNotFoundException;
        } catch (Exception $e) {
            Log::error('JournalRepository::showJournalPage', [$e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Create a new journal entry for the authenticated user.
     *
     * This method accepts an array of credentials, including the title of the journal.
     * It associates the journal with the currently authenticated user and saves it to the database.
     * If an error occurs during the creation, the error is logged, and the exception is re-thrown.
     *
     * @param array $credentials An array containing the journal's attributes (e.g., 'title').
     *
     * @return \App\Models\Journal The created journal instance.
     *
     * @throws \Exception If an error occurs while creating the journal.
     */
    public function createJournal(string $title, int $userId)
    {
        try {
            $journal = Journal::create([
                'user_id' => $userId,
                'title' => $title,
            ]);
            return $journal;
        } catch (Exception $e) {
            Log::error('JournalRepository::createJournal', [$e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Creates a new journal page for the given journal and content.
     *
     * This method creates a new `JournalPage` record associated with the provided `journalId`
     * and the given `content`. After successfully creating the journal page, it returns the
     * associated `Journal` model, including its related `JournalPages`.
     *
     * @param string $content The content of the journal page.
     * @param int $journalId The ID of the journal to which the page belongs.
     * @throws \Exception If there is an error during the creation process.
     */
    public function createJournalPage(string $content, int $journalId)
    {
        try {
            $page =  JournalPage::create([
                'journal_id' => $journalId,
                'content' => $content,
            ]);

            return Journal::with(['journalNotification', 'JournalPages' => function ($query) {
                $query->latest()->limit(1);
            }])->findOrFail($journalId);
        } catch (Exception $e) {
            Log::error('JournalRepository::createJournal', [$e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Create a journal notification based on provided credentials and journal ID.
     *
     * @param array $credentials The reminder credentials (type and time).
     * @param int $journalId The ID of the journal for which the notification is created.
     * @throws \Exception If an error occurs during the creation of the notification.
     */
    public function createJournalNotification(array $credentials, int $journalId)
    {
        try {
            JournalNotification::create([
                'journal_id' => $journalId,
                'type' => $credentials['reminder_type'],
                'time' => $credentials['reminder_time'],
            ]);

            return Journal::with('JournalPages')->findOrFail($journalId);
        } catch (Exception $e) {
            Log::error('JournalRepository::createJournalNotification', [$e->getMessage()]);
            throw $e;
        }
    }


    /**
     * Save an image URL associated with a journal page.
     *
     * @param string $url The URL of the image.
     * @param int $page_id The ID of the journal page where the image will be saved.
     * @throws \Exception If an error occurs during the saving of the image.
     */
    public function saveJournalImage(string $url, int $page_id)
    {
        try {
            Image::create([
                'url' => $url,
                'journal_page_id' => $page_id,
            ]);
        } catch (Exception $e) {
            Log::error('JournalRepository::saveJournalImage', [$e->getMessage()]);
            throw $e;
        }
    }


    /**
     * Get the next page number for a journal.
     *
     * @param int $journalId The ID of the journal.
     * @throws \Exception If an error occurs during the retrieval of the next page.
     */
    public function nextPageOfJournal(int $journalId)
    {
        try {
            $lastJournalPage = Journal::find($journalId)
                ->JournalPages()
                ->latest('id')
                ->first();

            $newPageNumber = JournalPage::latest('id')
                ->first();
            return $lastJournalPage ? $lastJournalPage->id + 1 : $newPageNumber + 1;
        } catch (Exception $e) {
            Log::error('JournalRepository::lastPageOfJournal', [$e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Toggle the archive status of a given journal.
     *
     * This method inverts the current 'archive' status of the provided
     * Journal model and saves the updated state to the database. If an error
     * occurs during the save operation, it logs the error message and throws
     * the exception again.
     *
     * @param Journal $journal The journal whose archive status is to be toggled.
     * @throws Exception If there is an error during the save operation.
     */
    public function toggleArchive(int $id)
    {
        try {
            $journal = Journal::find($id);
            $journal->archive = !$journal->archive;
            $journal->save();
        } catch (Exception $e) {
            Log::error('JournalRepository::toggleArvice', [$e->getMessage()]);
            throw $e;
        }
    }


    /**
     * Search for journals by title and retrieve the oldest journal page for each.
     *
     * This method searches for journals whose titles contain the specified search term.
     * It returns all journals that match the title search along with the oldest associated
     * journal page (ordered by ID).
     * @throws Exception If there is an error during the database query.
     */
    public function searchJournalByTitle(string $title, int $userId)
    {
        try {
            $journals = Journal::select('id', 'title', 'created_at')->whereUserId($userId)->whereArchive(false)->where('title', 'like', '%' . $title . '%')
                ->with(['JournalPages' => function ($query) {
                    $query->orderBy('id', 'asc')->limit(1);
                }])
                ->get();

            $journals->makeVisible('created_at');
            return $journals;
        } catch (Exception $e) {
            Log::error('JournalRepository::searchJournalByTitle', [$e->getMessage()]);
            throw $e;
        }
    }
}
