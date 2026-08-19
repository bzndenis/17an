<?php

namespace App\Models;

use App\Enums\ParticipantStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Participant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'category_id',
        'name',
        'number',
        'gender',
        'dob',
        'phone',
        'address',
        'rt_rw',
        'team',
        'status',
        'photo',
    ];

    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'dob' => 'date',
            'status' => ParticipantStatus::class,
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ParticipantCategory::class, 'category_id');
    }

    public function competitions(): BelongsToMany
    {
        return $this->belongsToMany(Competition::class, 'competition_participants')
            ->withPivot('seed')
            ->withTimestamps();
    }

    public function competitionEntries(): HasMany
    {
        return $this->hasMany(CompetitionParticipant::class);
    }

    public function matchParticipants(): HasMany
    {
        return $this->hasMany(MatchParticipant::class);
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
