<?php

namespace App\Services\API\V1\Journal;

use App\Repositories\API\V1\Journal\JournalRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Log;

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

        } catch (Exception $e) {
            Log::error('JournalService::createJournal', [$e->getMessage()]);
            throw $e;
        }
    }
}
