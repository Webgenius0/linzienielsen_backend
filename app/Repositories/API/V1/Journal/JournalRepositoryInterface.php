<?php

namespace App\Repositories\API\V1\Journal;

interface JournalRepositoryInterface
{
    /**
     * Create a new journal entry for the authenticated user.
     *
     * This method accepts an array of credentials, including the title of the journal.
     * It associates the journal with the currently authenticated user and saves it to the database.
     * If an error occurs during the creation, the error is logged, and the exception is re-thrown.
     *
     * @param array $credentials An array containing the journal's attributes (e.g., 'title').
     */
    public function createJournal(string $title);


    public function createJournalPage(string $content, int $journalId);
}
