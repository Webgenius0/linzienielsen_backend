<?php

namespace App\Services\API\V1\Journal;

use Exception;
use Illuminate\Support\Facades\Log;

class JournalService
{


    public function CreateJournal(array $credentials)
    {
        try {
            
        } catch (Exception $e) {
            Log::error('JournalService::CreateJournal', [$e->getMessage()]);
            throw $e;
        }
    }
}
