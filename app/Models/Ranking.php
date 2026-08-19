<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ranking extends Model
{
    protected $fillable = [
        'competition_id',
        'participant_id',
        'played',
        'won',
        'drawn',
        'lost',
        'points',
        'bonus',
    ];

    protected function casts(): array
    {
        return [
            'played' => 'integer',
            'won' => 'integer',
            'drawn' => 'integer',
            'lost' => 'integer',
            'points' => 'integer',
            'bonus' => 'integer',
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
