<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Round extends Model
{
    protected $fillable = [
        'competition_id',
        'name',
        'round_number',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'round_number' => 'integer',
        ];
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'round_id');
    }
}
