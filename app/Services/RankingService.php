<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\Ranking;
use Illuminate\Support\Collection;

class RankingService
{
    public function __construct(
        protected EventService $eventService,
    ) {}

    public function getLeaderboard(Competition $competition): Collection
    {
        return Ranking::with('participant.category')
            ->where('competition_id', $competition->id)
            ->orderByDesc('points')
            ->orderByDesc('won')
            ->orderBy('lost')
            ->get()
            ->values()
            ->map(function ($ranking, $index) {
                $ranking->rank = $index + 1;

                return $ranking;
            });
    }

    public function getGlobalRanking(array $filters = []): Collection
    {
        $eventId = $this->eventService->requireActiveEventId();

        $query = Ranking::with(['participant.category', 'competition'])
            ->whereHas('competition', fn ($q) => $q->where('event_id', $eventId));

        if (! empty($filters['competition_id'])) {
            $query->where('competition_id', $filters['competition_id']);
        }

        if (! empty($filters['category_id'])) {
            $query->whereHas('participant', fn ($q) => $q->where('category_id', $filters['category_id']));
        }

        if (! empty($filters['gender'])) {
            $query->whereHas('participant', fn ($q) => $q->where('gender', $filters['gender']));
        }

        if (! empty($filters['team'])) {
            $query->whereHas('participant', fn ($q) => $q->where('team', $filters['team']));
        }

        return $query->get()
            ->groupBy('participant_id')
            ->map(function ($rankings) {
                $participant = $rankings->first()->participant;

                return (object) [
                    'participant' => $participant,
                    'competitions_count' => $rankings->count(),
                    'played' => $rankings->sum('played'),
                    'won' => $rankings->sum('won'),
                    'drawn' => $rankings->sum('drawn'),
                    'lost' => $rankings->sum('lost'),
                    'points' => $rankings->sum('points') + $rankings->sum('bonus'),
                ];
            })
            ->sortByDesc('points')
            ->values()
            ->map(function ($item, $index) {
                $item->rank = $index + 1;

                return $item;
            });
    }

    public function getChartData(Collection $rankings): array
    {
        $top = $rankings->take(10);

        return [
            'labels' => $top->pluck('participant.name')->toArray(),
            'points' => $top->pluck('points')->toArray(),
            'wins' => $top->pluck('won')->toArray(),
        ];
    }
}
