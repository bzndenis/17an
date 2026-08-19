<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchResult extends Model
{
    protected $fillable = [
        'match_id',
        'winner_id',
        'notes',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'finished_at' => 'datetime',
        ];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'match_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'winner_id');
    }
}
