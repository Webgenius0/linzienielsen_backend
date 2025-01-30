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


    /**
     * Get the url attribute.
     *
     * @param string|null $url The URL to be processed. Can be null or a string.
     *
     * @return string The processed URL. It may be modified or default to a fallback image.
     */
    public function getUrlAttribute($url): string
    {
        if ($url) {
            if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
                return $url;
            } else {
                return asset('storage/' . $url);
            }
        } else {
            return asset('assets/custom/img/user.jpg');
        }
    }
}
