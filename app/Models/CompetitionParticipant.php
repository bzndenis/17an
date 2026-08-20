<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionParticipant extends Model
{
    protected $fillable = [
        'competition_id',
        'participant_id',
        'seed',
        'group_number',
    ];

    protected function casts(): array
    {
        return [
            'seed' => 'integer',
            'group_number' => 'integer',
        ];
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
