<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Journal extends Model
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
        'deleted_at',
    ];


    /**
     * accessor for created_at attribute
     */
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d F Y');
    }


    /**
     * Define the relationship between the current model and the User model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define the relationship between the current model and the JournalPage model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function JournalPages(): HasMany
    {
        return $this->hasMany(JournalPage::class);
    }

    /**
     * Define the relationship between the current model and the JournalNotification model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function journalNotification(): HasOne
    {
        return $this->hasOne(JournalNotification::class);
    }
}
