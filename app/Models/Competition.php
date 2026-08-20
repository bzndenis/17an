<?php

namespace App\Models;

use App\Enums\CompetitionStatus;
use App\Enums\CompetitionSystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Competition extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'name',
        'slug',
        'description',
        'category',
        'system',
        'status',
        'location',
        'start_at',
        'duration_minutes',
        'prize_1',
        'prize_2',
        'prize_3',
        'banner',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'system' => CompetitionSystem::class,
            'status' => CompetitionStatus::class,
            'start_at' => 'datetime',
            'duration_minutes' => 'integer',
            'config' => 'array',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Participant::class, 'competition_participants')
            ->withPivot('seed', 'group_number')
            ->withTimestamps();
    }

    public function competitionParticipants(): HasMany
    {
        return $this->hasMany(CompetitionParticipant::class);
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(Round::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(GameMatch::class);
    }

    public function rankings(): HasMany
    {
        return $this->hasMany(Ranking::class);
    }

    public function awards(): HasMany
    {
        return $this->hasMany(Award::class);
    }

    public function scopeForEvent($query, int $eventId)
    {
        return $query->where('event_id', $eventId);
    }
}
