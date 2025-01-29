<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Image extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Define the relationship between the current model and the JournalPage model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function journalPage(): BelongsTo
    {
        return $this->belongsTo(JournalPage::class);
    }
}
