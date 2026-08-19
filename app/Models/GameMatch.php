<?php

namespace App\Models;

use App\Enums\MatchStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GameMatch extends Model
{
    protected $table = 'matches';

    protected $fillable = [
        'round_id',
        'competition_id',
        'match_number',
        'status',
        'scheduled_at',
        'venue',
        'next_match_id',
        'bracket_position',
    ];

    protected function casts(): array
    {
        return [
            'match_number' => 'integer',
            'status' => MatchStatus::class,
            'scheduled_at' => 'datetime',
            'bracket_position' => 'integer',
        ];
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function nextMatch(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'next_match_id');
    }

    public function previousMatches(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'next_match_id');
    }

    public function matchParticipants(): HasMany
    {
        return $this->hasMany(MatchParticipant::class, 'match_id');
    }

    public function result(): HasOne
    {
        return $this->hasOne(MatchResult::class, 'match_id');
    }

    public function scopeForEvent($query, int $eventId)
    {
        return $query->whereHas('competition', fn ($q) => $q->where('event_id', $eventId));
    }
}
