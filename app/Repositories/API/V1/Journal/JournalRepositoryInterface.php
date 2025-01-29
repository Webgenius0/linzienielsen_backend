<?php

namespace App\Repositories\API\V1\Journal;

interface JournalRepositoryInterface
{
    public function createJournal(array $credentials);
}
