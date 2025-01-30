<?php

namespace App\Services\API\V1\Journal;

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
     * Creates a journal and processes its content, including uploading images.
     *
     * @param array $credentials The data to create the journal, including title, content, and images.
     *
     * @return mixed The created journal page with updated content.
     * @throws Exception If an error occurs during journal creation.
     */
    public function createJournal(array $credentials)
    {
        try {
            DB::beginTransaction();
            // Create the journal entry
            $journal = $this->journalRepositoryInterface->createJournal($credentials['title']);
            // Create the journal notification
            $this->journalRepositoryInterface->createJournalNotification($credentials, $journal->id);

            $response = $this->processHtmlContent($credentials['content'], $credentials['images'], $journal->id);

            // Get and process the HTML content
            $htmlContent = $response[0];
            $imageUrl = $response[1];

            // Create a new journal page with the updated HTML content
            $journalWithPage = $this->journalRepositoryInterface->createJournalPage($htmlContent, $journal->id);
            $journalWithPageArray = json_decode($journalWithPage, true);
            $pageId = $journalWithPageArray['journal_pages'][0]['id'];

            foreach ($imageUrl as $url) {
                $this->journalRepositoryInterface->saveJournalImage($url, $pageId);
            }
            // Commit the transaction
            DB::commit();

            return $journalWithPage;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('JournalService::createJournal', [$e->getMessage()]);
            throw $e;
        }
    }


    /**
     * Processes the HTML content, including replacing image sources with uploaded images.
     *
     * @param string $htmlContent The HTML content to be processed.
     * @param array $uploadedImages The array of uploaded images.
     * @param int $journalId The ID of the journal.
     *
     */
    private function processHtmlContent(string $htmlContent, array $uploadedImages, int $journalId)
    {
        try {
            // Create a new DOMDocument instance to parse the HTML
            $dom = new DOMDocument();
            libxml_use_internal_errors(true); // Prevents errors from malformed HTML
            $dom->loadHTML($htmlContent); // Load the content directly, no need to wrap with <html> and <body>
            libxml_clear_errors(); // Clear any libxml errors

            // Replace image sources with uploaded images
            $imageurl = $this->processImagesInHtml($dom, $uploadedImages, $journalId);

            // After processing all images, get the content inside the <body> tag directly
            $updatedHtmlContent = $this->extractBodyContent($dom);

            // Replace escaped double quotes with single quotes in the `src` attributes
            return [$this->replaceDoubleQuotesWithSingle($updatedHtmlContent), $imageurl];
        } catch (Exception $e) {
            Log::error('JournalService::processHtmlContent', [$e->getMessage()]);
            throw $e;
        }
    }


    /**
     * Processes the <img> tags in the HTML and replaces their 'src' attribute with the uploaded image paths.
     *
     * @param DOMDocument $dom The DOMDocument instance containing the HTML content.
     * @param array $uploadedImages The array of uploaded images.
     * @param int $journalId The ID of the journal.
     */
    private function processImagesInHtml(DOMDocument $dom, array $uploadedImages, int $journalId)
    {
        try {
            $images = $dom->getElementsByTagName('img');
            $imageIndex = 0;
            $totalImages = count($uploadedImages);

            $imageUrl = [];

            // Loop through each <img> tag and each uploaded image
            foreach ($images as $img) {
                if ($imageIndex < $totalImages) {
                    // Get the current image file
                    $file = $uploadedImages[$imageIndex];

                    if ($file && $file instanceof UploadedFile) {
                        // Store the image in the journal's folder
                        $imageName = $file->store('journal/' . $journalId, 'public');

                        // Update the image src in the HTML content
                        $img->setAttribute('src', asset('storage/' . $imageName));
                        $imageUrl[] = $imageName;

                        // Move to the next image in the array
                        $imageIndex++;
                    }
                }
            }
            return $imageUrl;
        } catch (Exception $e) {
            Log::error('JournalService::processImagesInHtml', [$e->getMessage()]);
            throw $e;
        }
    }


    /**
     * Extracts and returns the content inside the <body> tag from the DOMDocument.
     *
     * @param DOMDocument $dom The DOMDocument instance containing the HTML content.
     *
     * @return string The extracted body content as a string.
     */
    private function extractBodyContent(DOMDocument $dom): string
    {
        try {
            $updatedHtmlContent = '';
            foreach ($dom->documentElement->childNodes as $node) {
                if ($node->nodeName === 'body') {
                    foreach ($node->childNodes as $child) {
                        $updatedHtmlContent .= $dom->saveHTML($child);
                    }
                }
            }
            return $updatedHtmlContent;
        } catch (Exception $e) {
            Log::error('JournalService::extractBodyContent', [$e->getMessage()]);
            throw $e;
        }
    }


    /**
     * Replaces escaped double quotes in the src attribute with single quotes and removes unnecessary whitespace.
     *
     * @param string $htmlContent The HTML content to be processed.
     *
     * @return string The updated HTML content with replaced quotes and removed whitespace.
     */
    private function replaceDoubleQuotesWithSingle(string $htmlContent): string
    {
        try {
            $htmlContent = preg_replace('/src=\\"(.*?)\\"/', "src='$1'", $htmlContent);
            return str_replace(["\n", "\r", "\t"], '', $htmlContent);
        } catch (Exception $e) {
            Log::error('JournalService::replaceDoubleQuotesWithSingle', [$e->getMessage()]);
            throw $e;
        }
    }
}
