<?php

namespace App\Services;

use App\Enums\CompetitionSystem;
use App\Enums\MatchStatus;
use App\Models\GameMatch;
use App\Models\MatchResult;
use Illuminate\Support\Facades\DB;

class MatchService
{
    public function __construct(
        protected EventService $eventService,
        protected BracketService $bracketService,
        protected PointSystemService $pointSystemService,
        protected ActivityLogService $activityLogService,
    ) {}

    public function paginate(array $filters = [], int $perPage = 15)
    {
        $eventId = $this->eventService->requireActiveEventId();

        $query = GameMatch::with([
            'competition',
            'round',
            'matchParticipants.participant',
            'result.winner',
        ])->forEvent($eventId);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['competition_id'])) {
            $query->where('competition_id', $filters['competition_id']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('venue', 'like', "%{$search}%")
                    ->orWhereHas('competition', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        return $query->orderBy('scheduled_at')->orderBy('match_number')->paginate($perPage)->withQueryString();
    }

    public function findForEvent(int $id): GameMatch
    {
        $eventId = $this->eventService->requireActiveEventId();

        return GameMatch::with([
            'competition',
            'round',
            'matchParticipants.participant',
            'result.winner',
            'nextMatch',
        ])
            ->forEvent($eventId)
            ->findOrFail($id);
    }

    public function updateResult(GameMatch $match, array $data): GameMatch
    {
        return DB::transaction(function () use ($match, $data) {
            foreach ($data['scores'] ?? [] as $participantId => $score) {
                $match->matchParticipants()
                    ->where('participant_id', $participantId)
                    ->update(['score' => (int) $score]);
            }

            if (isset($data['winner_id'])) {
                $match->matchParticipants()->update(['is_winner' => false]);
                $match->matchParticipants()
                    ->where('participant_id', $data['winner_id'])
                    ->update(['is_winner' => true]);
            }

            if (($data['status'] ?? null) === MatchStatus::Live->value) {
                $match->update(['status' => MatchStatus::Live]);
            }

            return $match->fresh(['matchParticipants.participant', 'result']);
        });
    }

    public function finishMatch(GameMatch $match, ?int $winnerId = null, ?string $notes = null): GameMatch
    {
        return DB::transaction(function () use ($match, $winnerId, $notes) {
            $match->load(['matchParticipants', 'competition']);

            if (! $winnerId) {
                $winner = $match->matchParticipants->sortByDesc('score')->first();
                $winnerId = $winner?->participant_id;
            }

            if ($winnerId) {
                $match->matchParticipants()->update(['is_winner' => false]);
                $match->matchParticipants()
                    ->where('participant_id', $winnerId)
                    ->update(['is_winner' => true]);
            }

            MatchResult::updateOrCreate(
                ['match_id' => $match->id],
                [
                    'winner_id' => $winnerId,
                    'notes' => $notes,
                    'finished_at' => now(),
                ]
            );

            $match->update(['status' => MatchStatus::Finished]);

            if ($match->competition->system === CompetitionSystem::Knockout && $winnerId) {
                $this->bracketService->advanceWinner($match, $winnerId);
            }

            if (in_array($match->competition->system, [CompetitionSystem::Point, CompetitionSystem::League], true)) {
                $this->pointSystemService->updateRanking($match);
            }

            $this->activityLogService->log(
                'match.finished',
                "Pertandingan #{$match->match_number} selesai.",
                ['match_id' => $match->id, 'winner_id' => $winnerId]
            );

            return $match->fresh(['matchParticipants.participant', 'result', 'competition']);
        });
    }
}
