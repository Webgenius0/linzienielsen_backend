<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Journal;
use App\Models\JournalPage;
use App\Models\User;

class JournalSeeder extends Seeder
{
    public function run()
    {
        $users = User::whereBetween('id', [1, 5])->get();

        foreach ($users as $user) {
            for ($j = 1; $j <= 50; $j++) {
                $journal = Journal::create([
                    'user_id' => $user->id,
                    'title' => "Journal $j of User {$user->id}",
                ]);

                for ($p = 1; $p <= 30; $p++) {
                    JournalPage::create([
                        'journal_id' => $journal->id,
                        'content' => $this->getSampleContent(),
                    ]);
                }
            }
        }

        foreach ($users as $user) {
            for ($j = 1; $j <= 50; $j++) {
                $journal = Journal::create([
                    'user_id' => $user->id,
                    'title' => "Journal $j of User {$user->id}",
                    'archive' => true,
                ]);
                for ($p = 1; $p <= 50; $p++) {
                    JournalPage::create([
                        'journal_id' => $journal->id,
                        'content' => $this->getSampleContent(),
                    ]);
                }
            }
        }
    }

    private function getSampleContent()
    {
        return "<h1>My First Heading</h1><p>My first paragraph.</p>
                <img src='https://linzienielsen.test/storage/journal/39/fyT3gKfVwOTsy0badiq5OY9WMxl6PeQET2hVv6hS.jpg'>
                <h1>My First Heading</h1><p>My first paragraph.</p>
                <img src='https://linzienielsen.test/storage/journal/39/lP6IBv2ozK4aazSYm1LSEgsonHIF28b6cINnnqZ5.webp'>";
    }


    // private function getSampleContent()
    // {
    //     return "<h1>My First Heading</h1><p>My first paragraph.</p>
    //             <img src='https://linzienielsen.softvencefsd.xyz/storage/journal/39/fyT3gKfVwOTsy0badiq5OY9WMxl6PeQET2hVv6hS.jpg'>
    //             <h1>My First Heading</h1><p>My first paragraph.</p>
    //             <img src='https://linzienielsen.softvencefsd.xyz/storage/journal/39/lP6IBv2ozK4aazSYm1LSEgsonHIF28b6cINnnqZ5.webp'>";
    // }
}
