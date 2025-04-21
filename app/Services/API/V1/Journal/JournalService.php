<?php

namespace App\Services\API\V1\Journal;

use App\Helpers\Helper;
use App\Models\Journal;
use App\Models\JournalPage;
use App\Repositories\API\V1\Journal\JournalRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
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
     * Creates a new journal, processes its content, and stores related data.
     *
     * This function handles the creation of a journal, including saving the journal's title,
     * associating it with a user, creating a journal notification, formatting the content into HTML,
     * processing any images, and creating a journal page with the formatted content.
     * If any step fails, the transaction is rolled back.
     *
     * @param array $credentials The journal creation data, including the title and content.
     * @return mixed The created journal page with the formatted content.
     * @throws Exception If an error occurs during the process, the exception is thrown after rolling back the transaction.
     */
    public function createJournal(array $credentials)
    {
        try {
            DB::beginTransaction();
            $journal = $this->journalRepositoryInterface->createJournal($credentials['title'], $this->user->id);
            // Create the journal notification
            $this->journalRepositoryInterface->createJournalNotification($credentials, $journal->id);

            $imageUrl = [];
            if (isset($credentials['images'])) {
                Log::info('tushar');
                foreach ($credentials['images'] as $image) {
                    $imageUrl[] = Helper::uploadFile($image, 'journal');
                }
            }

            $response = $this->htmlFormat($credentials['content'], $journal->id, $imageUrl);

            // Get and process the HTML content
            $htmlContent = $response[0];

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

            $imageUrl = [];
            foreach ($credentials['images'] as $image) {
                $imageUrl[] = Helper::uploadFile($image, 'journal');
            }

            $response = $this->htmlFormat($credentials['content'], $credentials['journal_id'], $imageUrl);

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


    /**
     * Searches for journals by title for the authenticated user.
     *
     * This function queries the journal repository for journals that match the given title
     * and are associated with the currently authenticated user. If an error occurs during
     * the search, it logs the error and throws an exception.
     *
     * @param string $title The title or partial title to search for.
     * @return mixed The list of journals matching the search criteria.
     * @throws Exception If an error occurs during the search process, the exception is thrown.
     */
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
                Log::info($url);
                $this->journalRepositoryInterface->saveJournalImage($url, $pageId);
            }
            return $journalWithPage;
        } catch (Exception $e) {
            Log::error('JournalService::createPage', [$e->getMessage()]);
            throw $e;
        }
    }


    /**
     * Formats the given content into HTML and processes images for storage.
     *
     * @param array|string $content The content to be formatted, expected as an array.
     * @param int $journalId The ID of the journal associated with the content.
     * @return array Returns an array containing the formatted HTML output and the list of stored image paths.
     */
    public function htmlFormat($content, $journalId, $images)
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
        $imagIndex = 0;

        foreach ($content as $item) {
            if (isset($item['insert']['_type']) && $item['insert']['_type'] === 'image') {
                $imagePath = $this->saveImage($item['insert']['source'], $journalId);
                $htmlOutput .= "<img src='" . asset('storage/' . $images[$imagIndex]) . "'>";
                $imagePaths[] = $imagePath;
                $imagIndex++;
            } elseif (isset($item['insert']) && is_string($item['insert'])) {
                $htmlOutput .= $this->formatText($item['insert'], $item['attributes'] ?? []);
            }
        }
        return [
            $htmlOutput,
            $imagePaths
        ];
    }

    /**
     * Saves an image to the storage directory for a specific journal.
     *
     * @param string $filePath The path of the image to be saved.
     * @param int $journalId The ID of the journal associated with the image.
     * @return string|null The stored image path or null if the file does not exist.
     */
    private function saveImage($filePath, $journalId)
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $imageData = file_get_contents($filePath);
        $filename = uniqid() . '.jpg';
        Storage::disk('public')->put('uploads/' . $journalId . '/' . $filename, $imageData);
        return 'uploads/' . $filename;
    }


    /**
     * Formats text with given attributes into HTML.
     *
     * @param string $text The text content to be formatted.
     * @param array $attributes An associative array of text formatting attributes (e.g., bold, italic, heading levels).
     * @return string The formatted HTML string.
     */
    private function formatText($text, $attributes)
    {
        $html = htmlentities($text);

        // Handle heading levels (h1 - h6)
        if (isset($attributes['h']) && in_array($attributes['h'], [1, 2, 3, 4, 5, 6])) {
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

    /**
     * generatePDF
     * @param \App\Models\Journal $journal
     * @return array{cover_url: string, pdf_url: string, total_pages: int}
     */
    public function generatePDF(Journal $journal)
    {
        try {
            // Generate the interior PDF
            $pdf = Pdf::loadView('journal.pdf', compact('journal'));
            $cover = Pdf::loadView('journal.cover', compact('journal'));

            $pdf->setPaper([0, 0, 432, 648], 'portrait');
            $cover->setPaper([0, 0, 432, 648], 'portrait');

            $pdf->getDomPDF()->set_option("isRemoteEnabled", true);

            // Save the generated PDFs
            $pdfPath = 'journal_pdfs/' . $journal->id . '.pdf';
            Storage::disk('public')->put($pdfPath, $pdf->output());
            $coverPath = 'journal_pdfs/' . $journal->id . '_cover.pdf';
            Storage::disk('public')->put($coverPath, $cover->output());

            // Get the total page count
            $dompdf = $pdf->getDomPDF();
            $canvas = $dompdf->get_canvas();
            $pageCount = $canvas->get_page_count();

            return [
                'cover_url' => asset('storage/' . $coverPath),
                'pdf_url' => asset('storage/' . $pdfPath),
                'total_pages' => $pageCount
            ];
        } catch (Exception $e) {
            Log::error('JournalService::generatePDF', [$e->getMessage()]);
            throw $e;
        }
    }
}
