<?php

namespace App\Services;

use App\Enums\CompetitionSystem;
use App\Enums\MatchStatus;
use App\Models\Competition;
use App\Models\CompetitionParticipant;
use App\Models\GameMatch;
use App\Models\MatchParticipant;
use App\Models\MatchResult;
use App\Models\Ranking;
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

    public function canRandomizeMatchups(Competition $competition): bool
    {
        if ($competition->matches()->doesntExist()) {
            return false;
        }

        return ! $competition->matches()
            ->whereIn('status', [MatchStatus::Finished, MatchStatus::Live, MatchStatus::Walkover])
            ->exists();
    }

    public function randomizeMatchups(Competition $competition): void
    {
        if (! $this->canRandomizeMatchups($competition)) {
            throw new \RuntimeException('Tidak bisa random ulang. Pastikan belum ada pertandingan yang selesai atau sedang live.');
        }

        DB::transaction(function () use ($competition) {
            match ($competition->system) {
                CompetitionSystem::Knockout => $this->randomizeKnockoutMatchups($competition),
                CompetitionSystem::Point, CompetitionSystem::League => $this->randomizePointMatchups($competition),
                default => throw new \RuntimeException('Random ulang belum didukung untuk sistem pertandingan ini.'),
            };

            $this->activityLogService->log(
                'matches.randomized',
                "Pasangan pertandingan lomba {$competition->name} di-random ulang.",
                ['competition_id' => $competition->id]
            );
        });
    }

    protected function randomizeKnockoutMatchups(Competition $competition): void
    {
        $firstRound = $competition->rounds()->orderBy('round_number')->first();

        if (! $firstRound) {
            throw new \RuntimeException('Bracket belum dibuat. Generate bracket terlebih dahulu.');
        }

        $firstRoundMatches = $firstRound->matches()->orderBy('match_number')->get();

        if ($firstRoundMatches->isEmpty()) {
            throw new \RuntimeException('Tidak ada pertandingan babak pertama.');
        }

        $this->clearAllMatchAssignments($competition);

        $participantIds = $competition->participants()
            ->pluck('participants.id')
            ->shuffle()
            ->values()
            ->toArray();

        $this->updateSeeds($competition, $participantIds);

        $bracketSize = $firstRoundMatches->count() * 2;
        while (count($participantIds) < $bracketSize) {
            $participantIds[] = null;
        }

        foreach ($firstRoundMatches as $index => $match) {
            foreach ([$participantIds[$index * 2] ?? null, $participantIds[$index * 2 + 1] ?? null] as $participantId) {
                if ($participantId) {
                    MatchParticipant::create([
                        'match_id' => $match->id,
                        'participant_id' => $participantId,
                        'score' => 0,
                        'is_winner' => false,
                    ]);
                }
            }
        }
    }

    protected function randomizePointMatchups(Competition $competition): void
    {
        $matches = $competition->matches()->orderBy('match_number')->get();
        $participantIds = $competition->participants()
            ->pluck('participants.id')
            ->shuffle()
            ->values()
            ->toArray();

        if (count($participantIds) < 2) {
            throw new \RuntimeException('Minimal 2 peserta diperlukan.');
        }

        $pairs = [];
        for ($i = 0; $i < count($participantIds); $i++) {
            for ($j = $i + 1; $j < count($participantIds); $j++) {
                $pairs[] = [$participantIds[$i], $participantIds[$j]];
            }
        }
        shuffle($pairs);

        $this->clearAllMatchAssignments($competition);

        foreach ($matches as $index => $match) {
            if (! isset($pairs[$index])) {
                continue;
            }

            foreach ($pairs[$index] as $participantId) {
                MatchParticipant::create([
                    'match_id' => $match->id,
                    'participant_id' => $participantId,
                    'score' => 0,
                    'is_winner' => false,
                ]);
            }
        }

        Ranking::where('competition_id', $competition->id)->update([
            'played' => 0,
            'won' => 0,
            'drawn' => 0,
            'lost' => 0,
            'points' => 0,
            'bonus' => 0,
        ]);
    }

    protected function clearAllMatchAssignments(Competition $competition): void
    {
        $matchIds = $competition->matches()->pluck('id');

        MatchParticipant::whereIn('match_id', $matchIds)->delete();
        MatchResult::whereIn('match_id', $matchIds)->delete();

        $competition->matches()->update(['status' => MatchStatus::Scheduled]);
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
