<?php

namespace App\Repositories\API\V1\Journal;

use Exception;
use Illuminate\Support\Facades\Log;

class JournalRepository implements JournalRepositoryInterface
{
    public function createJournal(array $credentials)
    {
        try {

        } catch (Exception $e) {
            Log::error('JournalRepository::createJournal', [$e->getMessage()]);
            throw $e;
        }
    }
}
