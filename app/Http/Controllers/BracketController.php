<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Services\BracketService;
use App\Services\CompetitionService;
use App\Services\MatchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BracketController extends Controller
{
    public function __construct(
        protected BracketService $bracketService,
        protected CompetitionService $competitionService,
        protected MatchService $matchService,
    ) {}

    public function show(Competition $competition): View
    {
        $competition = $this->competitionService->findForEvent($competition->id);

        $this->bracketService->fixRoundNames($competition);

        $rounds = $competition->rounds()
            ->with(['matches.matchParticipants.participant', 'matches.result.winner'])
            ->orderBy('round_number')
            ->get();

        $groupEntries = $competition->competitionParticipants()
            ->with('participant')
            ->whereNotNull('group_number')
            ->orderBy('group_number')
            ->orderBy('seed')
            ->get()
            ->groupBy('group_number');

        return view('brackets.show', [
            'competition' => $competition,
            'rounds' => $rounds,
            'groupEntries' => $groupEntries,
            'canRandomizeMatches' => $this->matchService->canRandomizeMatchups($competition),
        ]);
    }

    public function generate(Request $request, Competition $competition): RedirectResponse
    {
        $competition = $this->competitionService->findForEvent($competition->id);

        try {
            if ($competition->matches()->exists() && ! $this->matchService->canRandomizeMatchups($competition)) {
                return back()->with('error', 'Tidak bisa regenerate. Ada pertandingan yang sudah live/selesai.');
            }

            // Acak seed dulu supaya pasangan di bracket benar-benar berubah
            $this->matchService->shuffleParticipantSeeds($competition);
            $this->bracketService->generateBracket($competition->fresh());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Bracket berhasil dibuat ulang dengan pasangan acak.');
    }

    public function randomize(Competition $competition): RedirectResponse
    {
        $competition = $this->competitionService->findForEvent($competition->id);

        try {
            $this->matchService->randomizeMatchups($competition);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Peserta berhasil diacak.');
    }

    public function update(Request $request, Competition $competition): RedirectResponse
    {
        $competition = $this->competitionService->findForEvent($competition->id);

        $validated = $request->validate([
            'participant_ids' => ['required', 'array', 'min:2'],
            'participant_ids.*' => ['exists:participants,id'],
            'seeds' => ['nullable', 'array'],
        ]);

        $this->competitionService->syncParticipants(
            $competition,
            $validated['participant_ids'],
            $validated['seeds'] ?? []
        );

        return back()->with('success', 'Peserta bracket berhasil diperbarui.');
    }
}
