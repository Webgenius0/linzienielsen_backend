<?php

namespace App\Services\API\V1\Journal;

use App\Models\Journal;
use App\Models\JournalPage;
use App\Repositories\API\V1\Journal\JournalRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Log;
use DOMDocument;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class JournalService
{
    protected JournalRepositoryInterface $journalRepositoryInterface;
    protected $user;

    /**
     * JournalService constructor.
     *
     * @param JournalRepositoryInterface $journalRepositoryInterface The repository interface for journal operations.
     */
    public function __construct(JournalRepositoryInterface $journalRepositoryInterface)
    {
        $this->journalRepositoryInterface = $journalRepositoryInterface;
        $this->user = Auth::user();
    }


    /**
     * Retrieve a list of public journals for the authenticated user.
     *
     * @return mixed A list of public journals.
     * @throws Exception If an error occurs while retrieving journals.
     */
    public function getJournals()
    {
        try {
            return $this->journalRepositoryInterface->listUserPublicJournals($this->user->id);
        } catch (Exception $e) {
            Log::error('JournalService::getJournals', [$e->getMessage()]);
            throw $e;
        }
    }


    /**
     * Retrieve a list of archived journals for the authenticated user.
     *
     * @return mixed A list of archived journals.
     * @throws Exception If an error occurs while retrieving archived journals.
     */
    public function getArchiveJournals()
    {
        try {
            return $this->journalRepositoryInterface->listUserArchivedJournals($this->user->id);
        } catch (Exception $e) {
            Log::error('JournalService::getArchiveJournals', [$e->getMessage()]);
            throw $e;
        }
    }


    /**
     * Retrieve a list of pages for a specific journal.
     *
     * @param int $journalId The ID of the journal.
     * @return mixed A list of journal pages.
     * @throws Exception If an error occurs while retrieving journal pages.
     */
    public function getJournalPages($journalId)
    {
        try {
            return $this->journalRepositoryInterface->listJournalPages($journalId);
        } catch (ModelNotFoundException $modelNotFoundException) {
            throw $modelNotFoundException;
        } catch (Exception $e) {
            Log::error('JournalService::getJournalPages', [$e->getMessage()]);
            throw $e;
        }
    }



    /**
     * Retrieve details of a specific journal page.
     *
     * @param int $pageId The ID of the journal page.
     * @return mixed The details of the journal page.
     * @throws Exception If an error occurs while retrieving the journal page.
     */
    public function showJournalPage($pageId)
    {
        try {
            return $this->journalRepositoryInterface->showJournalPage($pageId);
        } catch (ModelNotFoundException $modelNotFoundException) {
            throw $modelNotFoundException;
        } catch (Exception $e) {
            Log::error('JournalService::showJournalPage', [$e->getMessage()]);
            throw $e;
        }
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
            $user =             // Create the journal entry
                $journal = $this->journalRepositoryInterface->createJournal($credentials['title'], $this->user->id);
            // Create the journal notification
            $this->journalRepositoryInterface->createJournalNotification($credentials, $journal->id);

            $response = $this->processHtmlContent($credentials['content'], $credentials['images'], $journal->id);

            // Get and process the HTML content
            $htmlContent = $response[0];
            $imageUrl = $response[1];

            // Create a new journal page with the updated HTML content
            $journalWithPage = $this->createPage($htmlContent, $imageUrl, $journal->id);
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
     * Create a new journal page by processing HTML content and images.
     *
     * @param array $credentials The credentials containing content, images, and journal ID.
     * @throws Exception If an error occurs during the journal page creation process.
     */
    public function createJournalPage(array $credentials)
    {
        try {
            DB::beginTransaction();

            $response = $this->processHtmlContent($credentials['content'], $credentials['images'], $credentials['journal_id']);

            // Get and process the HTML content
            $htmlContent = $response[0];
            $imageUrl = $response[1];

            // Create a new journal page with the updated HTML content
            $journalWithPage = $this->createPage($htmlContent, $imageUrl, $credentials['journal_id']);
            DB::commit();

            return $journalWithPage;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('JournalService::createJournalPage', [$e->getMessage()]);
            throw $e;
        }
    }


    /**
     * Toggle the archive status of the given journal.
     *
     * This method calls the repository method to toggle the archive status
     * of the provided journal. If an error occurs, it logs the error and
     * throws the exception.
     *
     * @param Journal $journal The journal whose archive status is to be toggled.
     * @throws Exception If an error occurs during the repository operation.
     */
    public function toggleArchive($credentials)
    {
        try {
            $this->journalRepositoryInterface->toggleArchive($credentials['journal_id']);
        } catch (Exception $e) {
            Log::error('JournalService::arciveJournal', [$e->getMessage()]);
            throw $e;
        }
    }



    public function searchJournal($title)
    {
        try {
            $journals = $this->journalRepositoryInterface->searchJournalByTitle($title, $this->user->id);
            return $journals;
        } catch (Exception $e) {
            Log::error('JournalService::searchJournal', [$e->getMessage()]);
            throw $e;
        }
    }



    /**
     * Delete a journal entry by ID.
     *
     * This method attempts to find and delete a journal entry. It ensures that
     * the authenticated user owns the journal before deleting it. If the journal
     * is not found, or if the user lacks permission, appropriate exceptions are thrown.
     *
     * @param  int  $journalId  The ID of the journal to be deleted.
     * @throws ModelNotFoundException If the journal does not exist.
     * @throws AccessDeniedHttpException If the user is not authorized to delete the journal.
     * @throws Exception If any other error occurs during deletion.
     */
    public function deleteJournal($journalId)
    {
        try {
            $journal = Journal::findOrFail($journalId);
            if ($journal->user_id != $this->user->id) {
                throw new AccessDeniedHttpException();
            }
            $journal->delete();
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (AccessDeniedHttpException $accessDeniedHttpException) {
            throw $accessDeniedHttpException;
        } catch (Exception $e) {
            Log::error('JournalService::deleteJournal', [$e->getMessage()]);
            throw $e;
        }
    }



    /**
     * Delete a journal page.
     *
     * This method retrieves a journal page by its ID, checks if the authenticated
     * user owns the associated journal, and deletes the journal page if authorized.
     *
     * It handles the following exceptions:
     * - ModelNotFoundException: If the journal page does not exist.
     * - AccessDeniedHttpException: If the user is not authorized to delete the journal page.
     * - General Exception: Logs any unexpected errors and rethrows them.
     *
     * @param  int  $journalPageId  The ID of the journal page to delete.
     * @return void
     *
     * @throws ModelNotFoundException If the journal page is not found.
     * @throws AccessDeniedHttpException If the user does not have permission to delete the journal page.
     * @throws Exception If any other error occurs.
     */
    public function deleteJournalPage($journalPageId)
    {
        try {
            $journalPage = JournalPage::with(['journal'])->findOrFail($journalPageId);
            Log::info($journalPage);
            if ($journalPage->journal->user_id != $this->user->id) {
                throw new AccessDeniedHttpException();
            }
            $journalPage->delete();
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (AccessDeniedHttpException $accessDeniedHttpException) {
            throw $accessDeniedHttpException;
        } catch (Exception $e) {
            Log::error('JournalService::deleteJournalPage', [$e->getMessage()]);
            throw $e;
        }
    }


    // -------------------------------------------------------------------------------------------
    // -------------------------------------------------------------------------------------------
    // -------------------------------------------------------------------------------------------


    /**
     * Create a journal page and associate images with it.
     *
     * @param string $htmlContent The HTML content to be added to the journal page.
     * @param array $imageUrl An array of image URLs to be associated with the page.
     * @param int $jouranlId The ID of the journal where the page is created.
     * @throws Exception If an error occurs during page creation or image saving.
     */
    public function createPage(string $htmlContent, array $imageUrl, int $jouranlId)
    {
        try {
            // Create a new journal page with the updated HTML content
            $journalWithPage = $this->journalRepositoryInterface->createJournalPage($htmlContent, $jouranlId);
            $journalWithPageArray = json_decode($journalWithPage, true);
            $pageId = $journalWithPageArray['journal_pages'][0]['id'];

            foreach ($imageUrl as $url) {
                $this->journalRepositoryInterface->saveJournalImage($url, $pageId);
            }
            return $journalWithPage;
        } catch (Exception $e) {
            Log::error('JournalService::createPage', [$e->getMessage()]);
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


    public function store($content)
    {
        if (!is_array($content)) {
            Log::error('JournalService::store - Invalid content received', ['content' => $content]);
            return [
                'status' => 'error',
                'message' => 'Invalid content format'
            ];
        }

        $htmlOutput = '';
        $imagePaths = [];

        foreach ($content as $item) {
            if (isset($item['insert']['_type']) && $item['insert']['_type'] === 'image') {
                $imagePath = $this->saveImage($item['insert']['source']);
                $htmlOutput .= "<img src='" . asset('storage/' . $imagePath) . "'>";
                $imagePaths[] = $imagePath;
            } elseif (isset($item['insert']) && is_string($item['insert'])) {
                $htmlOutput .= $this->formatText($item['insert'], $item['attributes'] ?? []);
            }
        }

        return [
            'status' => 'success',
            'html' => $htmlOutput,
            'image_paths' => $imagePaths
        ];
    }


    private function saveImage($filePath)
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $imageData = file_get_contents($filePath);
        $filename = uniqid() . '.jpg';
        Storage::disk('public')->put('uploads/' . $filename, $imageData);
        return 'uploads/' . $filename;
    }

    private function formatText($text, $attributes)
    {
        $html = htmlentities($text);

        // Handle heading levels (h1 - h6)
        if (isset($attributes['h']) && in_array($attributes['h'], [1, 2, 3, 4, 5, 6]))
        {
            $html = "<h{$attributes['h']}>$html</h{$attributes['h']}>";
        } else {
            // Default to paragraph if no heading is specified
            $html = "<p>$html</p>";
        }

        // Apply other text styles
        if (isset($attributes['b'])) $html = "<b>$html</b>";
        if (isset($attributes['i'])) $html = "<i>$html</i>";
        if (isset($attributes['u'])) $html = "<u>$html</u>";
        if (isset($attributes['s'])) $html = "<s>$html</s>";
        if (isset($attributes['color'])) $html = "<span style='color: {$attributes['color']}'>$html</span>";
        if (isset($attributes['size'])) $html = "<span style='font-size: {$attributes['size']}px'>$html</span>";

        return $html;
    }
}
