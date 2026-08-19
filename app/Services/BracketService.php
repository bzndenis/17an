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
                        ['match_id' => $nextMatch->id, 'participant_id' => $winnerParticipantId],
                        ['score' => 0, 'is_winner' => false]
                    );
                }
            }

            $this->activityLogService->log(
                'bracket.advanced',
                "Pemenang pertandingan #{$match->match_number} maju ke babak berikutnya.",
                ['match_id' => $match->id, 'winner_id' => $winnerParticipantId]
            );
        });
    }

    public function resetBracket(Competition $competition): void
    {
        $matchIds = $competition->matches()->pluck('id');
        MatchParticipant::whereIn('match_id', $matchIds)->delete();
        MatchResult::whereIn('match_id', $matchIds)->delete();
        $competition->matches()->delete();
        $competition->rounds()->delete();
    }

    public function fixRoundNames(Competition $competition): void
    {
        $rounds = $competition->rounds()->orderBy('round_number')->get();
        if ($rounds->isEmpty()) {
            return;
        }

        $names = $this->getRoundNames($rounds->count());
        foreach ($rounds as $index => $round) {
            if (isset($names[$index]) && $round->name !== $names[$index]) {
                $round->update(['name' => $names[$index]]);
            }
        }
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

    // ─── Knockout ───────────────────────────────────────────────

    protected function generateKnockoutBracket(Competition $competition): Collection
    {
        $participantCount = $competition->participants()->count();

        if ($participantCount < 2) {
            throw new \RuntimeException('Minimal 2 peserta diperlukan.');
        }

        $bracketSize = (int) pow(2, (int) ceil(log($participantCount, 2)));

        $participants = $competition->competitionParticipants()
            ->with('participant')
            ->orderBy('seed')
            ->get()
            ->pluck('participant.id')
            ->toArray();

        $seeded = $this->standardBracketSeeding($participants, $bracketSize);
        $firstRoundSlots = $bracketSize / 2;

        // Determine actual matches vs byes in the first round
        $actualPairs = []; // slotIndex => [homeId, awayId]
        $byeAdvances = []; // slotIndex => participantId

        for ($i = 0; $i < $firstRoundSlots; $i++) {
            $home = $seeded[$i * 2] ?? null;
            $away = $seeded[$i * 2 + 1] ?? null;

            if ($home && $away) {
                $actualPairs[$i] = [$home, $away];
            } elseif ($home) {
                $byeAdvances[$i] = $home;
            } elseif ($away) {
                $byeAdvances[$i] = $away;
            }
        }

        $hasFirstRound = count($actualPairs) > 0;
        $totalFullRounds = (int) log($bracketSize, 2);
        $roundNames = $this->getRoundNames($totalFullRounds);

        // Create rounds — skip R1 if all are byes
        $startRound = $hasFirstRound ? 0 : 1;
        $rounds = collect();

        for ($i = $startRound; $i < $totalFullRounds; $i++) {
            $rounds->push(Round::create([
                'competition_id' => $competition->id,
                'name' => $roundNames[$i] ?? "Babak ".($i + 1),
                'round_number' => $i + 1,
                'type' => 'knockout',
            ]));
        }

        // Create matches per round
        $allMatches = collect();
        $matchNumber = 1;
        $matchesByFullRound = []; // indexed by full round index (0-based)

        foreach ($rounds as $round) {
            $fullRoundIndex = $round->round_number - 1;
            $matchesInRound = (int) ($bracketSize / pow(2, $fullRoundIndex + 1));
            $roundMatches = collect();

            if ($fullRoundIndex === 0 && $hasFirstRound) {
                // R1: only create matches for actual pairs
                foreach ($actualPairs as $slotIndex => $pair) {
                    $match = GameMatch::create([
                        'round_id' => $round->id,
                        'competition_id' => $competition->id,
                        'match_number' => $matchNumber++,
                        'status' => MatchStatus::Scheduled,
                        'bracket_position' => $slotIndex + 1,
                        'venue' => $competition->location,
                        'scheduled_at' => $competition->start_at,
                    ]);

                    foreach ($pair as $pid) {
                        MatchParticipant::create([
                            'match_id' => $match->id,
                            'participant_id' => $pid,
                            'score' => 0,
                            'is_winner' => false,
                        ]);
                    }

                    $roundMatches->push($match);
                    $allMatches->push($match);
                }
            } else {
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
            }

            $matchesByFullRound[$fullRoundIndex] = $roundMatches;
        }

        // Link next_match_id for rounds R2+ (non-R1 standard rounds)
        $fullRoundKeys = array_keys($matchesByFullRound);
        for ($k = 0; $k < count($fullRoundKeys) - 1; $k++) {
            $curKey = $fullRoundKeys[$k];
            $nextKey = $fullRoundKeys[$k + 1];

            if ($curKey === 0 && $hasFirstRound) {
                continue; // R1 linking handled separately below
            }

            $curMatches = $matchesByFullRound[$curKey];
            $nextMatches = $matchesByFullRound[$nextKey];

            foreach ($curMatches as $pos => $match) {
                $nextIdx = (int) floor($pos / 2);
                if (isset($nextMatches[$nextIdx])) {
                    $match->update(['next_match_id' => $nextMatches[$nextIdx]->id]);
                }
            }
        }

        // Link R1 matches → R2 using original slot index
        if ($hasFirstRound && isset($matchesByFullRound[1])) {
            $r2Matches = $matchesByFullRound[1];
            $actualSlots = array_keys($actualPairs);

            foreach ($matchesByFullRound[0] as $pos => $match) {
                $originalSlot = $actualSlots[$pos];
                $r2Idx = (int) floor($originalSlot / 2);
                if (isset($r2Matches[$r2Idx])) {
                    $match->update(['next_match_id' => $r2Matches[$r2Idx]->id]);
                }
            }
        }

        // Place bye advances into the appropriate second-round match
        $secondRoundKey = $hasFirstRound ? 1 : $startRound;
        if (isset($matchesByFullRound[$secondRoundKey])) {
            $targetMatches = $matchesByFullRound[$secondRoundKey];
            foreach ($byeAdvances as $slotIndex => $pid) {
                $targetIdx = (int) floor($slotIndex / 2);
                if (isset($targetMatches[$targetIdx])) {
                    MatchParticipant::firstOrCreate(
                        ['match_id' => $targetMatches[$targetIdx]->id, 'participant_id' => $pid],
                        ['score' => 0, 'is_winner' => false]
                    );
                }
            }
        }

        return $allMatches;
    }

    // ─── Point / Round-Robin ────────────────────────────────────

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

    // ─── Seeding ────────────────────────────────────────────────

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
            $sum = (int) pow(2, $r + 1) - 1;
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
