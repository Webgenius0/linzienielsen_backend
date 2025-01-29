<?php

namespace App\Services\API\V1\Journal;

use App\Helpers\Helper;
use App\Repositories\API\V1\Journal\JournalRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Log;
use DOMDocument;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class JournalService
{
    protected JournalRepositoryInterface $journalRepositoryInterface;

    public function __construct(JournalRepositoryInterface $journalRepositoryInterface)
    {
        $this->journalRepositoryInterface = $journalRepositoryInterface;
    }


    /**
     * Create a new journal entry with associated HTML content and images.
     *
     * This method accepts journal credentials, including a title and content. It creates a new journal entry
     * for the authenticated user and associates it with a unique page containing the provided HTML content.
     * Any images embedded within the HTML content are uploaded and stored in a designated folder,
     * and their `src` attributes are updated to point to the stored images.
     *
     * A database transaction is used to ensure the creation of the journal entry and the associated page is atomic.
     * If an error occurs during the process, the transaction is rolled back and the exception is re-thrown.
     *
     * @param array $credentials An array containing the journal's attributes:
     *                           - 'title' (string) => The title of the journal.
     *                           - 'content' (string) => The HTML content to be processed.
     *                           - 'images' (array) => Array of uploaded images to be linked in the content.
     *
     * @throws \Exception If an error occurs during journal or image creation or processing.
     */
    public function createJournal(array $credentials)
    {
        try {
            DB::beginTransaction();

            // Create the journal entry
            $journal = $this->journalRepositoryInterface->createJournal($credentials['title']);
            // Create the journal notification
            $this->journalRepositoryInterface->createJournalNotification($credentials, $journal->id);
            // Get the HTML content from the request
            $htmlContent = $credentials['content'];

            // Create a new DOMDocument instance to parse the HTML
            $dom = new DOMDocument();
            libxml_use_internal_errors(true); // Prevents errors from malformed HTML
            $dom->loadHTML($htmlContent); // Load the content directly, no need to wrap with <html> and <body>
            libxml_clear_errors(); // Clear any libxml errors

            // Find all <img> tags in the HTML
            $images = $dom->getElementsByTagName('img');

            // Check if there are multiple images uploaded
            if ($credentials['images'] && is_array($credentials['images'])) {
                $uploadedImages = $credentials['images']; // Assuming 'images' is the array of files uploaded
                $imageIndex = 0; // Index to keep track of which image we're processing
                $totalImages = count($uploadedImages); // Total number of images

                // Loop through each <img> tag and each uploaded image
                foreach ($images as $img) {
                    // If we still have images left to upload
                    if ($imageIndex < $totalImages) {
                        // Get the current image file
                        $file = $uploadedImages[$imageIndex];

                        // Store the image in the desired path
                        if ($file && $file instanceof UploadedFile) {
                            // Store the image in the journal's folder
                            $imageName = $file->store('journal/' . $journal->id, 'public');

                            // Update the image src in the HTML content
                            $img->setAttribute('src', asset('storage/' . $imageName));

                            // Move to the next image in the array
                            $imageIndex++;
                        }
                    }
                }
            }

            // After processing all images, get the content inside the <body> tag directly
            $updatedHtmlContent = '';
            foreach ($dom->documentElement->childNodes as $node) {
                // Append only the body content, without <html> and <body> tags
                if ($node->nodeName === 'body') {
                    foreach ($node->childNodes as $child) {
                        $updatedHtmlContent .= $dom->saveHTML($child);
                    }
                }
            }
            // Replace escaped double quotes with single quotes in the `src` attributes
            $updatedHtmlContent = preg_replace('/src=\\"(.*?)\\"/', "src='$1'", $updatedHtmlContent);

            // Remove unwanted newline characters from the HTML content
            $updatedHtmlContent = str_replace(["\n", "\r", "\t"], '', $updatedHtmlContent);

            // Create a new journal page with the updated HTML content
            $journalWithPage = $this->journalRepositoryInterface->createJournalPage($updatedHtmlContent, $journal->id);

            // Commit the transaction
            DB::commit();

            return $journalWithPage;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('JournalService::createJournal', [$e->getMessage()]);
            throw $e;
        }
    }
}
