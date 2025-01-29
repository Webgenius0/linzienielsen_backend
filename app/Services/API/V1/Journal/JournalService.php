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

            $journal = $this->journalRepositoryInterface->createJournal($credentials['title']);

            $htmlContent = $credentials['content'];

            // Create a new DOMDocument instance to parse HTM
            $dom = new DOMDocument();
            libxml_use_internal_errors(true); // Prevents errors from being thrown due to malformed HTML
            $dom->loadHTML(mb_convert_encoding($htmlContent, 'HTML-ENTITIES', 'UTF-8'));
            libxml_clear_errors(); // Clear any libxml errors

            // Find all <img> tags in the HTML
            $images = $dom->getElementsByTagName('img');

            // Loop through each <img> tag
            foreach ($images as $img) {
                // Get the current 'src' value of the image
                $src = $img->getAttribute('src');

                // Check if the image is in base64 format (i.e., not a URL)
                if (strpos($src, 'data:image') === 0) {
                    // Extract base64 encoded image data and its MIME type
                    preg_match('/data:image\/(?<mime>\w+);base64,(?<data>.+)/', $src, $matches);

                    // Skip processing if the image data is invalid
                    if (!isset($matches['mime']) || !isset($matches['data'])) {
                        continue;
                    }

                    $imageData = base64_decode($matches['data']); // Decode base64 data
                    $extension = $matches['mime']; // Extract MIME type (png, jpeg, etc.)

                    // Create a temporary file for the base64 image data
                    $tempFile = tmpfile();
                    fwrite($tempFile, $imageData);
                    $imagePath = stream_get_meta_data($tempFile)['uri'];
                    $image = new UploadedFile($imagePath, 'image.' . $extension);

                    // Use the uploadFile function to store the image
                    $imageName = Helper::uploadFile($image, 'journal/'.$journal->id);
                    $img->setAttribute('src', $imageName);
                }
            }

            $updatedHtmlContent = $dom->saveHTML();

            $journalWithPage = $this->journalRepositoryInterface->createJournalPage($updatedHtmlContent, $journal->id);
            DB::commit();

            return $journalWithPage;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('JournalService::createJournal', [$e->getMessage()]);
            throw $e;
        }
    }


}
