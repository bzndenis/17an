<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchParticipant extends Model
{
    protected $fillable = [
        'match_id',
        'participant_id',
        'score',
        'is_winner',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'is_winner' => 'boolean',
        ];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'match_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
