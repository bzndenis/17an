<?php

namespace App\Services;

use App\Enums\CompetitionSystem;
use App\Enums\MatchStatus;
use App\Models\Competition;
use App\Models\CompetitionParticipant;
use App\Models\GameMatch;
use App\Models\MatchParticipant;
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
            $match->load(['matchParticipants', 'competition', 'round']);

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

            $system = $match->competition->system;
            $roundType = $match->round?->type;

            $isKnockoutAdvance = $system === CompetitionSystem::Knockout
                || ($system === CompetitionSystem::GroupKnockout && $roundType === 'knockout');

            if ($isKnockoutAdvance && $winnerId) {
                $this->bracketService->advanceWinner($match, $winnerId);
            }

            $isGroupOrPoint = in_array($system, [
                CompetitionSystem::Point,
                CompetitionSystem::League,
            ], true) || ($system === CompetitionSystem::GroupKnockout && $roundType === 'group');

            if ($isGroupOrPoint) {
                $this->pointSystemService->updateRanking($match);
            }

            if ($system === CompetitionSystem::GroupKnockout && $roundType === 'group') {
                $this->bracketService->tryAdvanceFromGroups($match->competition->fresh(['rankings', 'rounds', 'competitionParticipants']));
            }

            $this->activityLogService->log(
                'match.finished',
                "Pertandingan #{$match->match_number} selesai.",
                ['match_id' => $match->id, 'winner_id' => $winnerId]
            );

            return $match->fresh(['matchParticipants.participant', 'result', 'competition']);
        });
    }

    public function canRandomizeMatchups(Competition $competition): bool
    {
        if ($competition->participants()->count() < 2) {
            return false;
        }

        if ($competition->matches()->doesntExist()) {
            return true;
        }

        return ! $competition->matches()
            ->whereIn('status', [MatchStatus::Finished, MatchStatus::Live, MatchStatus::Walkover])
            ->exists();
    }

    public function randomizeMatchups(Competition $competition): void
    {
        if (! $this->canRandomizeMatchups($competition)) {
            throw new \RuntimeException('Tidak bisa mengacak peserta. Pastikan belum ada pertandingan yang selesai atau sedang live.');
        }

        DB::transaction(function () use ($competition) {
            $participantIds = $competition->participants()
                ->pluck('participants.id')
                ->shuffle()
                ->values()
                ->toArray();

            $this->updateSeeds($competition, $participantIds);

            if ($competition->matches()->exists()) {
                $this->bracketService->generateBracket($competition->fresh());
            } elseif ($competition->system === CompetitionSystem::GroupKnockout) {
                $entries = $competition->competitionParticipants()->orderBy('seed')->get();
                $groupCount = max(2, (int) ($competition->config['group_count'] ?? 2));
                $this->bracketService->assignParticipantsToGroups($entries, $groupCount);
            }

            $this->activityLogService->log(
                'matches.randomized',
                "Peserta lomba {$competition->name} diacak ulang.",
                ['competition_id' => $competition->id]
            );
        });
    }

    protected function updateSeeds(Competition $competition, array $participantIds): void
    {
        foreach ($participantIds as $index => $participantId) {
            CompetitionParticipant::where('competition_id', $competition->id)
                ->where('participant_id', $participantId)
                ->update(['seed' => $index + 1]);
        }
    }
}
