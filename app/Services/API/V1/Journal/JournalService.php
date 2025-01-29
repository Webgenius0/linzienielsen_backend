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

    public function createJournal(array $credentials)
    {
        try {
            DB::beginTransaction();

            // Create the journal entry
            $journal = $this->journalRepositoryInterface->createJournal($credentials['title']);

            // Get the HTML content from the request
            $htmlContent = $credentials['content'];

            // Create a new DOMDocument instance to parse the HTML
            $dom = new DOMDocument();
            libxml_use_internal_errors(true); // Prevents errors from malformed HTML
            $dom->loadHTML($htmlContent); // Load the content directly, no need to wrap with <html> and <body>
            libxml_clear_errors(); // Clear any libxml errors

            // Find all <img> tags in the HTML
            $images = $dom->getElementsByTagName('img');

            // Loop through each <img> tag
            foreach ($images as $img) {
                $src = $img->getAttribute('src');

                // Check if the image is in base64 format (i.e., not a URL)
                if (strpos($src, 'data:image') === 0) {
                    preg_match('/data:image\/(?<mime>\w+);base64,(?<data>.+)/', $src, $matches);

                    if (!isset($matches['mime']) || !isset($matches['data'])) {
                        continue;
                    }

                    $imageData = base64_decode($matches['data']);
                    $extension = $matches['mime'];

                    $tempFile = tmpfile();
                    fwrite($tempFile, $imageData);
                    $imagePath = stream_get_meta_data($tempFile)['uri'];

                    // Create an UploadedFile instance from the temporary file
                    $image = new UploadedFile($imagePath, 'image.' . $extension);

                    // Use the uploadFile function to store the image
                    $imageName = Helper::uploadFile($image, 'journal/' . $journal->id);

                    // Update the image src in the HTML content
                    $img->setAttribute('src', asset('storage/' . $imageName));
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

            // Remove unwanted newline characters from the HTML content
            $updatedHtmlContent = str_replace(["\n"], '', $updatedHtmlContent);

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
