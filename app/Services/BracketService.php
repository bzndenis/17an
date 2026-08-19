<?php

namespace App\Services;

use App\Enums\CompetitionSystem;
use App\Enums\MatchStatus;
use App\Models\Competition;
use App\Models\CompetitionParticipant;
use App\Models\GameMatch;
use App\Models\MatchParticipant;
use App\Models\MatchResult;
use App\Models\Round;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BracketService
{
    public function __construct(
        protected ActivityLogService $activityLogService,
    ) {}

    public function generateBracket(Competition $competition): Collection
    {
        return DB::transaction(function () use ($competition) {
            $this->resetBracket($competition);
            $this->seedParticipants($competition);

            $matches = $competition->system === CompetitionSystem::Point
                ? $this->generatePointMatches($competition)
                : $this->generateKnockoutBracket($competition);

            $this->activityLogService->log(
                'bracket.generated',
                "Bracket lomba {$competition->name} dibuat.",
                ['competition_id' => $competition->id]
            );

            return $matches;
        });
    }

    public function seedParticipants(Competition $competition): void
    {
        $entries = CompetitionParticipant::where('competition_id', $competition->id)
            ->orderByRaw('seed IS NULL, seed ASC')
            ->orderBy('id')
            ->get();

        $seed = 1;
        foreach ($entries as $entry) {
            if (! $entry->seed) {
                $entry->update(['seed' => $seed]);
            }
            $seed++;
        }
    }

    public function createRounds(Competition $competition): Collection
    {
        $participantCount = $competition->participants()->count();

        if ($participantCount < 2) {
            throw new \RuntimeException('Minimal 2 peserta diperlukan untuk membuat bracket.');
        }

        $bracketSize = (int) pow(2, (int) ceil(log($participantCount, 2)));
        $totalRounds = (int) log($bracketSize, 2);
        $rounds = collect();
        $roundNames = $this->getRoundNames($totalRounds);

        for ($i = 1; $i <= $totalRounds; $i++) {
            $rounds->push(Round::create([
                'competition_id' => $competition->id,
                'name' => $roundNames[$i - 1] ?? "Babak {$i}",
                'round_number' => $i,
                'type' => 'knockout',
            ]));
        }

        return $rounds;
    }

    public function createMatches(Competition $competition, Collection $rounds): Collection
    {
        $participantCount = $competition->participants()->count();
        $bracketSize = (int) pow(2, (int) ceil(log($participantCount, 2)));
        $allMatches = collect();
        $matchNumber = 1;
        $matchesByRound = [];

        foreach ($rounds as $roundIndex => $round) {
            $matchesInRound = (int) ($bracketSize / pow(2, $roundIndex + 1));
            $roundMatches = collect();

            for ($m = 0; $m < $matchesInRound; $m++) {
                $match = GameMatch::create([
                    'round_id' => $round->id,
                    'competition_id' => $competition->id,
                    'match_number' => $matchNumber++,
                    'status' => MatchStatus::Scheduled,
                    'bracket_position' => $m + 1,
                    'venue' => $competition->location,
                    'scheduled_at' => $competition->start_at,
                ]);

                $roundMatches->push($match);
                $allMatches->push($match);
            }

            $matchesByRound[$roundIndex] = $roundMatches;
        }

        foreach ($matchesByRound as $roundIndex => $roundMatches) {
            if (! isset($matchesByRound[$roundIndex + 1])) {
                continue;
            }

            $nextRoundMatches = $matchesByRound[$roundIndex + 1];

            foreach ($roundMatches as $position => $match) {
                $nextMatchIndex = (int) floor($position / 2);
                $match->update(['next_match_id' => $nextRoundMatches[$nextMatchIndex]->id]);
            }
        }

        $this->assignFirstRoundParticipants($competition, $matchesByRound[0] ?? collect());

        return $allMatches;
    }

    public function advanceWinner(GameMatch $match, int $winnerParticipantId): void
    {
        DB::transaction(function () use ($match, $winnerParticipantId) {
            $match->matchParticipants()->update(['is_winner' => false]);
            $match->matchParticipants()
                ->where('participant_id', $winnerParticipantId)
                ->update(['is_winner' => true]);

            if ($match->next_match_id) {
                $nextMatch = GameMatch::find($match->next_match_id);

                if ($nextMatch) {
                    MatchParticipant::firstOrCreate(
                        [
                            'match_id' => $nextMatch->id,
                            'participant_id' => $winnerParticipantId,
                        ],
                        ['score' => 0, 'is_winner' => false]
                    );
                }
            }

            $this->activityLogService->log(
                'bracket.advanced',
                "Pemenang pertandingan #{$match->match_number} maju ke babak berikutnya.",
                [
                    'match_id' => $match->id,
                    'winner_id' => $winnerParticipantId,
                ]
            );
        });
    }

    public function resetBracket(Competition $competition): void
    {
        $competition->matches()->delete();
        $competition->rounds()->delete();
    }

    public function fixRoundNames(Competition $competition): void
    {
        $rounds = $competition->rounds()->orderBy('round_number')->get();
        $totalRounds = $rounds->count();

        if ($totalRounds === 0) {
            return;
        }

        $names = $this->getRoundNames($totalRounds);

        foreach ($rounds as $index => $round) {
            if (isset($names[$index]) && $round->name !== $names[$index]) {
                $round->update(['name' => $names[$index]]);
            }
        }
    }

    protected function generateKnockoutBracket(Competition $competition): Collection
    {
        $rounds = $this->createRounds($competition);

        return $this->createMatches($competition, $rounds);
    }

    protected function generatePointMatches(Competition $competition): Collection
    {
        $round = Round::create([
            'competition_id' => $competition->id,
            'name' => 'Fase Liga',
            'round_number' => 1,
            'type' => 'league',
        ]);

        $participantIds = $competition->participants()->pluck('participants.id')->toArray();
        $allMatches = collect();
        $matchNumber = 1;

        for ($i = 0; $i < count($participantIds); $i++) {
            for ($j = $i + 1; $j < count($participantIds); $j++) {
                $match = GameMatch::create([
                    'round_id' => $round->id,
                    'competition_id' => $competition->id,
                    'match_number' => $matchNumber++,
                    'status' => MatchStatus::Scheduled,
                    'venue' => $competition->location,
                    'scheduled_at' => $competition->start_at,
                ]);

                MatchParticipant::create([
                    'match_id' => $match->id,
                    'participant_id' => $participantIds[$i],
                    'score' => 0,
                ]);

                MatchParticipant::create([
                    'match_id' => $match->id,
                    'participant_id' => $participantIds[$j],
                    'score' => 0,
                ]);

                $allMatches->push($match);
            }
        }

        return $allMatches;
    }

    protected function assignFirstRoundParticipants(Competition $competition, Collection $firstRoundMatches): void
    {
        $participants = $competition->competitionParticipants()
            ->with('participant')
            ->orderBy('seed')
            ->get()
            ->pluck('participant');

        $bracketSize = $firstRoundMatches->count() * 2;
        $seeded = $this->standardBracketSeeding($participants->pluck('id')->toArray(), $bracketSize);

        foreach ($firstRoundMatches as $index => $match) {
            $homeId = $seeded[$index * 2] ?? null;
            $awayId = $seeded[$index * 2 + 1] ?? null;

            foreach ([$homeId, $awayId] as $participantId) {
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

    protected function standardBracketSeeding(array $participantIds, int $bracketSize): array
    {
        while (count($participantIds) < $bracketSize) {
            $participantIds[] = null;
        }

        return $participantIds;
    }

    protected function getRoundNames(int $totalRounds): array
    {
        $names = [];

        for ($i = 1; $i <= $totalRounds; $i++) {
            $matchesInRound = (int) pow(2, $totalRounds - $i);

            $names[] = match (true) {
                $matchesInRound === 1 => 'Final',
                $matchesInRound === 2 => 'Semifinal',
                $matchesInRound === 4 => 'Perempat Final',
                $matchesInRound === 8 => 'Babak 16 Besar',
                default => "Babak {$i}",
            };
        }

        return $names;
    }
}
