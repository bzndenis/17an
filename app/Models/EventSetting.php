<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSetting extends Model
{
    protected $fillable = [
        'event_id',
        'logo',
        'theme_color',
        'venue_default',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
