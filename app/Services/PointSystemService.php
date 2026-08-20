<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\GameMatch;
use App\Models\Ranking;
use Illuminate\Support\Facades\DB;

class PointSystemService
{
    public function calculatePoints(bool $isWin, bool $isDraw, array $config = []): int
    {
        $winPoints = $config['win_points'] ?? 3;
        $drawPoints = $config['draw_points'] ?? 1;
        $lossPoints = $config['loss_points'] ?? 0;

        if ($isWin) {
            return $winPoints;
        }

        if ($isDraw) {
            return $drawPoints;
        }

        return $lossPoints;
    }

    public function initializeRankings(Competition $competition): void
    {
        foreach ($competition->participants as $participant) {
            Ranking::firstOrCreate(
                [
                    'competition_id' => $competition->id,
                    'participant_id' => $participant->id,
                ],
                [
                    'played' => 0,
                    'won' => 0,
                    'drawn' => 0,
                    'lost' => 0,
                    'points' => 0,
                    'bonus' => 0,
                ]
            );
        }
    }

    public function updateRanking(GameMatch $match): void
    {
        $match->load(['matchParticipants', 'competition']);
        $participants = $match->matchParticipants;

        if ($participants->count() < 2) {
            return;
        }

        $config = $match->competition->config ?? [];
        $sides = $participants->groupBy(fn ($mp) => $mp->side ?? $mp->id);

        if ($sides->count() < 2) {
            return;
        }

        $sideScores = $sides->map(fn ($group) => (int) $group->first()->score);
        $maxScore = $sideScores->max();
        $sidesWithMax = $sideScores->filter(fn ($score) => $score === $maxScore);
        $isDraw = $sidesWithMax->count() > 1;

        DB::transaction(function () use ($match, $sides, $sideScores, $maxScore, $isDraw, $config) {
            foreach ($sides as $side => $members) {
                $score = $sideScores[$side];
                $isWin = ! $isDraw && $score === $maxScore;
                $isLoss = ! $isDraw && $score < $maxScore;
                $points = $this->calculatePoints($isWin, $isDraw, $config);

                foreach ($members as $mp) {
                    $ranking = Ranking::firstOrCreate(
                        [
                            'competition_id' => $match->competition_id,
                            'participant_id' => $mp->participant_id,
                        ]
                    );

                    $ranking->increment('played');
                    $ranking->increment('points', $points);

                    if ($isWin) {
                        $ranking->increment('won');
                    } elseif ($isDraw) {
                        $ranking->increment('drawn');
                    } elseif ($isLoss) {
                        $ranking->increment('lost');
                    }
                }
            }
        });
    }

    public function recalculateRanking(Competition $competition): void
    {
        DB::transaction(function () use ($competition) {
            Ranking::where('competition_id', $competition->id)->update([
                'played' => 0,
                'won' => 0,
                'drawn' => 0,
                'lost' => 0,
                'points' => 0,
            ]);

            $finishedMatches = $competition->matches()
                ->where('status', 'finished')
                ->with('matchParticipants')
                ->get();

            foreach ($finishedMatches as $match) {
                $this->updateRanking($match);
            }
        });
    }
}
