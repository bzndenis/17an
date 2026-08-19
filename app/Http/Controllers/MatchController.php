<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\GameMatch;
use App\Services\CompetitionService;
use App\Services\MatchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MatchController extends Controller
{
    public function __construct(
        protected MatchService $matchService,
        protected CompetitionService $competitionService,
    ) {}

    public function index(Request $request): View
    {
        return view('matches.index', [
            'matches' => $this->matchService->paginate($request->only([
                'status', 'competition_id', 'search',
            ])),
            'competitions' => Competition::forEvent(session('active_event_id'))
                ->orderBy('name')
                ->get(),
            'filters' => $request->only(['status', 'competition_id', 'search']),
        ]);
    }

    public function show(GameMatch $match): View
    {
        $match = $this->matchService->findForEvent($match->id);

        return view('matches.show', compact('match'));
    }

    public function storeResult(Request $request, GameMatch $match): RedirectResponse
    {
        $match = $this->matchService->findForEvent($match->id);

        $validated = $request->validate([
            'scores' => ['required', 'array'],
            'scores.*' => ['integer', 'min:0'],
            'winner_id' => ['nullable', 'exists:participants,id'],
            'notes' => ['nullable', 'string'],
            'finish' => ['nullable', 'boolean'],
        ]);

        $this->matchService->updateResult($match, $validated);

        if ($request->boolean('finish')) {
            $this->matchService->finishMatch($match, $validated['winner_id'] ?? null, $validated['notes'] ?? null);

            return back()->with('success', 'Hasil pertandingan berhasil disimpan dan pertandingan selesai.');
        }

        return back()->with('success', 'Hasil pertandingan berhasil diperbarui.');
    }
}
