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
    public function createJournalPage(string $content, int $journalId);


    public function createJournalNotification(array $credentials, int $journalId);


    public function saveJournalImage(string $url, int $page_id);
}
