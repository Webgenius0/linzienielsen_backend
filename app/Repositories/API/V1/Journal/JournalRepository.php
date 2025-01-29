<?php

namespace App\Repositories\API\V1\Journal;

use App\Models\Journal;
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


    public function createJournalPage(string $content, int $journalId)
    {
        try {
            $page =  JournalPage::create([
                'journal_id' => $journalId,
                'content' => $content,
            ]);

            return Journal::with('JournalPages')->findOrFail($journalId);
        } catch (Exception $e) {
            Log::error('JournalRepository::createJournal', [$e->getMessage()]);
            throw $e;
        }
    }
}
