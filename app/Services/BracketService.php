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
        $participants = $competition->competitionParticipants()
            ->with('participant')
            ->orderBy('seed')
            ->get()
            ->pluck('participant');

        $seeded = $this->standardBracketSeeding($participants->pluck('id')->toArray(), $bracketSize);
        $firstRoundSlots = $bracketSize / 2;

        $byeAdvances = [];
        $actualFirstRoundPairs = [];

        for ($i = 0; $i < $firstRoundSlots; $i++) {
            $homeId = $seeded[$i * 2] ?? null;
            $awayId = $seeded[$i * 2 + 1] ?? null;

            if ($homeId && $awayId) {
                $actualFirstRoundPairs[$i] = [$homeId, $awayId];
            } elseif ($homeId) {
                $byeAdvances[$i] = $homeId;
            } elseif ($awayId) {
                $byeAdvances[$i] = $awayId;
            }
        }

        $allMatches = collect();
        $matchNumber = 1;
        $matchesByRound = [];

        if (count($actualFirstRoundPairs) > 0) {
            $firstRound = $rounds->first();
            $roundMatches = collect();

            foreach ($actualFirstRoundPairs as $slotIndex => $pair) {
                $match = GameMatch::create([
                    'round_id' => $firstRound->id,
                    'competition_id' => $competition->id,
                    'match_number' => $matchNumber++,
                    'status' => MatchStatus::Scheduled,
                    'bracket_position' => $slotIndex + 1,
                    'venue' => $competition->location,
                    'scheduled_at' => $competition->start_at,
                ]);

                foreach ($pair as $participantId) {
                    MatchParticipant::create([
                        'match_id' => $match->id,
                        'participant_id' => $participantId,
                        'score' => 0,
                        'is_winner' => false,
                    ]);
                }

                $roundMatches->push($match);
                $allMatches->push($match);
            }

            $matchesByRound[0] = $roundMatches;
        } else {
            $rounds->shift();
        }

        $startRound = count($actualFirstRoundPairs) > 0 ? 1 : 0;

        foreach ($rounds as $roundIndex => $round) {
            if ($roundIndex === 0 && count($actualFirstRoundPairs) > 0) {
                continue;
            }

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

        $roundKeys = array_keys($matchesByRound);
        foreach ($roundKeys as $idx => $roundKey) {
            if (! isset($roundKeys[$idx + 1])) {
                continue;
            }
            $nextKey = $roundKeys[$idx + 1];
            $currentMatches = $matchesByRound[$roundKey];
            $nextMatches = $matchesByRound[$nextKey];

            foreach ($currentMatches as $position => $match) {
                $nextMatchIndex = (int) floor($position / 2);
                if (isset($nextMatches[$nextMatchIndex])) {
                    $match->update(['next_match_id' => $nextMatches[$nextMatchIndex]->id]);
                }
            }
        }

        $secondRoundKey = count($actualFirstRoundPairs) > 0 ? 1 : array_key_first($matchesByRound);
        if ($secondRoundKey !== null && isset($matchesByRound[$secondRoundKey])) {
            $secondRoundMatches = $matchesByRound[$secondRoundKey];
            foreach ($byeAdvances as $slotIndex => $participantId) {
                $targetMatchIndex = (int) floor($slotIndex / 2);
                if (isset($secondRoundMatches[$targetMatchIndex])) {
                    MatchParticipant::firstOrCreate(
                        ['match_id' => $secondRoundMatches[$targetMatchIndex]->id, 'participant_id' => $participantId],
                        ['score' => 0, 'is_winner' => false]
                    );
                }
            }
        }

        if (count($actualFirstRoundPairs) > 0) {
            $firstRoundMatches = $matchesByRound[0];
            $nextKey = $roundKeys[1] ?? null;
            if ($nextKey !== null && isset($matchesByRound[$nextKey])) {
                $nextRoundMatches = $matchesByRound[$nextKey];
                $actualKeys = array_keys($actualFirstRoundPairs);
                foreach ($firstRoundMatches as $pos => $match) {
                    $originalSlot = $actualKeys[$pos];
                    $nextMatchIndex = (int) floor($originalSlot / 2);
                    if (isset($nextRoundMatches[$nextMatchIndex])) {
                        $match->update(['next_match_id' => $nextRoundMatches[$nextMatchIndex]->id]);
                    }
                }
            }
        }

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


    protected function standardBracketSeeding(array $participantIds, int $bracketSize): array
    {
        $order = $this->bracketSeedOrder($bracketSize);
        $result = array_fill(0, $bracketSize, null);

        foreach ($participantIds as $index => $id) {
            $position = $order[$index] ?? $index;
            $result[$position] = $id;
        }

        return $result;
    }

    protected function bracketSeedOrder(int $size): array
    {
        if ($size <= 1) {
            return [0];
        }

        $rounds = (int) log($size, 2);
        $order = [0, 1];

        for ($r = 1; $r < $rounds; $r++) {
            $newOrder = [];
            $sum = pow(2, $r + 1) - 1;
            foreach ($order as $pos) {
                $newOrder[] = $pos;
                $newOrder[] = $sum - $pos;
            }
            $order = $newOrder;
        }

        return $order;
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
