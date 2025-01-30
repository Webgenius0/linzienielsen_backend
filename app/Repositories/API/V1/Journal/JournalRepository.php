<?php

namespace App\Repositories\API\V1\Journal;

use App\Models\Image;
use App\Models\Journal;
use App\Models\JournalNotification;
use App\Models\JournalPage;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class JournalRepository implements JournalRepositoryInterface
{

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
    public function createJournal(string $title)
    {
        try {
            $user = Auth::user();
            $journal = Journal::create([
                'user_id' => $user->id,
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

            return Journal::with(['JournalPages', 'journalNotification'])->findOrFail($journalId);
        } catch (Exception $e) {
            Log::error('JournalRepository::createJournal', [$e->getMessage()]);
            throw $e;
        }
    }


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


    public function nextPageOfJournal(int $journalId)
    {
        try {
            $lastJournalPage = Journal::find($journalId)
                ->JournalPages()
                ->latest('id')
                ->first();

            $newPageNumber = JournalPage::latest('id')
            ->first();
            return $lastJournalPage ? $lastJournalPage->id + 1 : $newPageNumber +1;
        } catch (Exception $e) {
            Log::error('JournalRepository::lastPageOfJournal', [$e->getMessage()]);
            throw $e;
        }
    }
}
