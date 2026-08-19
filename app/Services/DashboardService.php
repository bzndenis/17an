<?php

namespace App\Services;

use App\Enums\MatchStatus;
use App\Enums\ParticipantStatus;
use App\Models\Award;
use App\Models\Competition;
use App\Models\GameMatch;
use App\Models\Participant;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(
        protected EventService $eventService,
    ) {}

    public function getStats(): array
    {
        $eventId = $this->eventService->requireActiveEventId();

        $totalParticipants = Participant::forEvent($eventId)->count();
        $totalCompetitions = Competition::forEvent($eventId)->count();
        $liveMatches = GameMatch::forEvent($eventId)->where('status', MatchStatus::Live)->count();
        $finishedMatches = GameMatch::forEvent($eventId)->where('status', MatchStatus::Finished)->count();
        $eliminatedParticipants = Participant::forEvent($eventId)
            ->where('status', ParticipantStatus::Eliminated)
            ->count();
        $totalAwards = Award::whereHas('competition', fn ($q) => $q->where('event_id', $eventId))->count();

        return [
            'total_participants' => $totalParticipants,
            'total_competitions' => $totalCompetitions,
            'live_matches' => $liveMatches,
            'finished_matches' => $finishedMatches,
            'eliminated_participants' => $eliminatedParticipants,
            'total_awards' => $totalAwards,
        ];
    }

    public function getRecentActivity(int $limit = 10): Collection
    {
        $eventId = $this->eventService->requireActiveEventId();

        return \App\Models\ActivityLog::with('user')
            ->forEvent($eventId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getUpcomingMatches(int $limit = 5): Collection
    {
        $eventId = $this->eventService->requireActiveEventId();

        return GameMatch::with([
            'competition',
            'matchParticipants.participant',
        ])
            ->forEvent($eventId)
            ->whereIn('status', [MatchStatus::Scheduled, MatchStatus::Live])
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->get();
    }

    public function getCompetitionOverview(): Collection
    {
        $eventId = $this->eventService->requireActiveEventId();

        return Competition::forEvent($eventId)
            ->withCount([
                'participants',
                'matches',
                'matches as finished_matches_count' => fn ($q) => $q->where('status', MatchStatus::Finished),
            ])
            ->orderBy('name')
            ->get()
            ->map(function (Competition $competition) {
                $totalMatches = $competition->matches_count;
                $finished = $competition->finished_matches_count;
                $competition->progress = $totalMatches > 0
                    ? round(($finished / $totalMatches) * 100)
                    : 0;

                return $competition;
            });
    }
}
