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

            $matches = match ($competition->system) {
                CompetitionSystem::Point, CompetitionSystem::League => $this->generatePointMatches($competition),
                CompetitionSystem::GroupKnockout => $this->generateGroupKnockoutBracket($competition),
                default => $this->generateKnockoutBracket($competition),
            };

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

    // ─── Group + Knockout ───────────────────────────────────────

    protected function generateGroupKnockoutBracket(Competition $competition): Collection
    {
        $groupCount = max(2, (int) ($competition->config['group_count'] ?? 2));
        $qualifyPerGroup = max(1, (int) ($competition->config['qualify_per_group'] ?? 2));

        $entries = $competition->competitionParticipants()
            ->orderBy('seed')
            ->get();

        $minParticipants = $groupCount * 2;
        if ($entries->count() < $minParticipants) {
            throw new \RuntimeException("Minimal {$minParticipants} peserta diperlukan untuk {$groupCount} grup.");
        }

        $groups = $this->assignParticipantsToGroups($entries, $groupCount);
        $allMatches = collect();
        $matchNumber = 1;
        $roundNumber = 1;

        foreach ($groups as $groupNum => $participantIds) {
            $round = Round::create([
                'competition_id' => $competition->id,
                'name' => 'Grup '.$this->groupLetter($groupNum),
                'round_number' => $roundNumber++,
                'type' => 'group',
            ]);

            $ids = $participantIds->values()->all();

            for ($i = 0; $i < count($ids); $i++) {
                for ($j = $i + 1; $j < count($ids); $j++) {
                    $match = GameMatch::create([
                        'round_id' => $round->id,
                        'competition_id' => $competition->id,
                        'match_number' => $matchNumber++,
                        'status' => MatchStatus::Scheduled,
                        'bracket_position' => $groupNum,
                        'venue' => $competition->location,
                        'scheduled_at' => $competition->start_at,
                    ]);

                    foreach ([$ids[$i], $ids[$j]] as $pid) {
                        MatchParticipant::create([
                            'match_id' => $match->id,
                            'participant_id' => $pid,
                            'score' => 0,
                            'is_winner' => false,
                        ]);
                    }

                    $allMatches->push($match);
                }
            }
        }

        $qualifierCount = $groupCount * $qualifyPerGroup;
        $knockoutMatches = $this->createEmptyKnockoutStructure($competition, $qualifierCount, $roundNumber, $matchNumber);
        $allMatches = $allMatches->merge($knockoutMatches);

        return $allMatches;
    }

    /**
     * Snake-draft participants into groups by seed order.
     *
     * @param  Collection<int, CompetitionParticipant>  $entries
     * @return array<int, Collection<int, int>>
     */
    public function assignParticipantsToGroups(Collection $entries, int $groupCount): array
    {
        $groups = [];
        for ($g = 1; $g <= $groupCount; $g++) {
            $groups[$g] = collect();
        }

        foreach ($entries->values() as $index => $entry) {
            $row = intdiv($index, $groupCount);
            $col = $index % $groupCount;
            $groupNum = $row % 2 === 0 ? ($col + 1) : ($groupCount - $col);

            $entry->update(['group_number' => $groupNum]);
            $groups[$groupNum]->push($entry->participant_id);
        }

        return $groups;
    }

    public function tryAdvanceFromGroups(Competition $competition): bool
    {
        if ($competition->system !== CompetitionSystem::GroupKnockout) {
            return false;
        }

        $groupRounds = $competition->rounds()->where('type', 'group')->get();
        if ($groupRounds->isEmpty()) {
            return false;
        }

        $pendingGroupMatches = $competition->matches()
            ->whereIn('round_id', $groupRounds->pluck('id'))
            ->where('status', '!=', MatchStatus::Finished)
            ->exists();

        if ($pendingGroupMatches) {
            return false;
        }

        $firstKnockoutRound = $competition->rounds()
            ->where('type', 'knockout')
            ->orderBy('round_number')
            ->first();

        if (! $firstKnockoutRound) {
            return false;
        }

        $firstRoundMatches = $firstKnockoutRound->matches()->orderBy('bracket_position')->orderBy('match_number')->get();
        $alreadySeeded = MatchParticipant::whereIn('match_id', $firstRoundMatches->pluck('id'))->exists();
        if ($alreadySeeded) {
            return false;
        }

        $qualifyPerGroup = max(1, (int) ($competition->config['qualify_per_group'] ?? 2));
        $qualifiers = $this->resolveGroupQualifiers($competition, $qualifyPerGroup);

        if ($qualifiers->isEmpty()) {
            return false;
        }

        $bracketSize = (int) pow(2, (int) ceil(log(max($qualifiers->count(), 2), 2)));
        $seeded = $this->standardBracketSeeding($qualifiers->all(), $bracketSize);
        $slots = (int) ($bracketSize / 2);

        $actualPairs = [];
        $byeAdvances = [];

        for ($i = 0; $i < $slots; $i++) {
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

        foreach ($firstRoundMatches as $pos => $match) {
            $slot = ($match->bracket_position ?? ($pos + 1)) - 1;
            if (! isset($actualPairs[$slot])) {
                continue;
            }

            foreach ($actualPairs[$slot] as $pid) {
                MatchParticipant::firstOrCreate(
                    ['match_id' => $match->id, 'participant_id' => $pid],
                    ['score' => 0, 'is_winner' => false]
                );
            }
        }

        $secondRound = $competition->rounds()
            ->where('type', 'knockout')
            ->where('round_number', '>', $firstKnockoutRound->round_number)
            ->orderBy('round_number')
            ->first();

        if ($secondRound) {
            $secondMatches = $secondRound->matches()->orderBy('bracket_position')->orderBy('match_number')->get();
            foreach ($byeAdvances as $slotIndex => $pid) {
                $targetIdx = (int) floor($slotIndex / 2);
                if (isset($secondMatches[$targetIdx])) {
                    MatchParticipant::firstOrCreate(
                        ['match_id' => $secondMatches[$targetIdx]->id, 'participant_id' => $pid],
                        ['score' => 0, 'is_winner' => false]
                    );
                }
            }
        }

        $this->activityLogService->log(
            'bracket.group_advanced',
            "Lolos fase grup lomba {$competition->name} dimasukkan ke bracket knockout.",
            ['competition_id' => $competition->id]
        );

        return true;
    }

    /**
     * @return Collection<int, int>
     */
    protected function resolveGroupQualifiers(Competition $competition, int $qualifyPerGroup): Collection
    {
        $competition->loadMissing('rankings');

        $entries = $competition->competitionParticipants()
            ->whereNotNull('group_number')
            ->get()
            ->groupBy('group_number');

        $winners = collect();
        $runners = collect();

        foreach ($entries->sortKeys() as $groupEntries) {
            $ranked = $groupEntries->sort(function ($a, $b) use ($competition) {
                $rankA = $competition->rankings->firstWhere('participant_id', $a->participant_id);
                $rankB = $competition->rankings->firstWhere('participant_id', $b->participant_id);

                return [$rankB?->points ?? 0, $rankB?->won ?? 0, -($b->seed ?? 999)]
                    <=> [$rankA?->points ?? 0, $rankA?->won ?? 0, -($a->seed ?? 999)];
            })->values();

            foreach ($ranked->take($qualifyPerGroup) as $index => $entry) {
                if ($index === 0) {
                    $winners->push($entry->participant_id);
                } else {
                    $runners->push($entry->participant_id);
                }
            }
        }

        return $winners->concat($runners)->values();
    }

    protected function createEmptyKnockoutStructure(
        Competition $competition,
        int $qualifierCount,
        int $startRoundNumber,
        int $startMatchNumber,
    ): Collection {
        $bracketSize = (int) pow(2, (int) ceil(log(max($qualifierCount, 2), 2)));
        $totalFullRounds = (int) log($bracketSize, 2);
        $roundNames = $this->getRoundNames($totalFullRounds);
        $rounds = collect();
        $allMatches = collect();
        $matchNumber = $startMatchNumber;
        $matchesByRound = [];

        for ($i = 0; $i < $totalFullRounds; $i++) {
            $round = Round::create([
                'competition_id' => $competition->id,
                'name' => $roundNames[$i] ?? 'Babak '.($i + 1),
                'round_number' => $startRoundNumber + $i,
                'type' => 'knockout',
            ]);
            $rounds->push($round);

            $matchesInRound = (int) ($bracketSize / pow(2, $i + 1));
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

            $matchesByRound[$i] = $roundMatches;
        }

        for ($i = 0; $i < count($matchesByRound) - 1; $i++) {
            $cur = $matchesByRound[$i];
            $next = $matchesByRound[$i + 1];
            foreach ($cur as $pos => $match) {
                $nextIdx = (int) floor($pos / 2);
                if (isset($next[$nextIdx])) {
                    $match->update(['next_match_id' => $next[$nextIdx]->id]);
                }
            }
        }

        return $allMatches;
    }

    protected function groupLetter(int $groupNum): string
    {
        return chr(64 + $groupNum);
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
