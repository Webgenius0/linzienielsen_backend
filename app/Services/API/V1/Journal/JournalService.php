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

    public function CreateJournal(array $credentials)
    {
        try {
            
        } catch (Exception $e) {
            Log::error('JournalService::CreateJournal', [$e->getMessage()]);
            throw $e;
        }
    }
}
