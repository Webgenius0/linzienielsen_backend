<?php

namespace App\Repositories\API\V1\Journal;

use App\Models\Journal;
use Exception;

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
     * @throws Exception If there is an error during the creation process.
     */
    public function createJournalPage(string $content, int $journalId);

    /**
     * Create a journal notification based on provided credentials and journal ID.
     *
     * @param array $credentials The reminder credentials (type and time).
     * @param int $journalId The ID of the journal for which the notification is created.
     * @throws Exception If an error occurs during the creation of the notification.
     */
    public function createJournalNotification(array $credentials, int $journalId);

    /**
     * Save an image URL associated with a journal page.
     *
     * @param string $url The URL of the image.
     * @param int $page_id The ID of the journal page where the image will be saved.
     * @throws Exception If an error occurs during the saving of the image.
     */
    public function saveJournalImage(string $url, int $page_id);

    /**
     * Get the next page number for a journal.
     *
     * @param int $journalId The ID of the journal.
     * @throws Exception If an error occurs during the retrieval of the next page.
     */
    public function nextPageOfJournal(int $journalId);


    /**
     * Toggle the archive status of a given journal.
     *
     * This method inverts the current 'archive' status of the provided
     * Journal model and saves the updated state to the database. If an error
     * occurs during the save operation, it logs the error message and throws
     * the exception again.
     * @throws Exception If there is an error during the save operation.
     */
    public function toggleArchive($id);
}
