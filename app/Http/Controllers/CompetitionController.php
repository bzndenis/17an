<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompetitionRequest;
use App\Http\Requests\UpdateCompetitionRequest;
use App\Models\Competition;
use App\Services\CompetitionService;
use App\Services\EventService;
use App\Services\MatchService;
use App\Services\ParticipantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompetitionController extends Controller
{
    public function __construct(
        protected CompetitionService $competitionService,
        protected ParticipantService $participantService,
        protected EventService $eventService,
        protected MatchService $matchService,
    ) {}

    public function index(Request $request): View
    {
        return view('competitions.index', [
            'competitions' => $this->competitionService->paginate($request->only([
                'search', 'status', 'system',
            ])),
            'filters' => $request->only(['search', 'status', 'system']),
        ]);
    }

    public function create(): View
    {
        return view('competitions.create');
    }

    public function store(StoreCompetitionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('banner')) {
            $data['banner'] = $request->file('banner')->store('competitions', 'public');
        }

        $playersPerSide = max(1, (int) ($data['players_per_side'] ?? 1));
        unset($data['players_per_side']);
        $data['config'] = array_merge($data['config'] ?? [], [
            'players_per_side' => $playersPerSide,
        ]);

        $competition = $this->competitionService->create($data);

        return redirect()->route('competitions.wizard', ['competition' => $competition, 'step' => 2])
            ->with('success', 'Lomba berhasil dibuat. Lanjutkan wizard pendaftaran peserta.');
    }

    public function show(Request $request, Competition $competition): View
    {
        $competition = $this->competitionService->findForEvent($competition->id);
        $tab = $request->get('tab', 'overview');
        $tabData = $this->competitionService->getShowTabData($competition, $tab);

        return view('competitions.show', array_merge(compact('competition', 'tab'), $tabData));
    }

    public function edit(Competition $competition): View
    {
        $competition = $this->competitionService->findForEvent($competition->id);

        return view('competitions.edit', compact('competition'));
    }

    public function update(UpdateCompetitionRequest $request, Competition $competition): RedirectResponse
    {
        $competition = $this->competitionService->findForEvent($competition->id);
        $data = $request->validated();

        if ($request->hasFile('banner')) {
            $data['banner'] = $request->file('banner')->store('competitions', 'public');
        }

        if (array_key_exists('players_per_side', $data)) {
            $data['config'] = array_merge($competition->config ?? [], [
                'players_per_side' => max(1, (int) $data['players_per_side']),
            ]);
            unset($data['players_per_side']);
        }

        $this->competitionService->update($competition, $data);

        return redirect()->route('competitions.show', $competition)
            ->with('success', 'Lomba berhasil diperbarui.');
    }

    public function destroy(Competition $competition): RedirectResponse
    {
        $competition = $this->competitionService->findForEvent($competition->id);
        $this->competitionService->delete($competition);

        return redirect()->route('competitions.index')
            ->with('success', 'Lomba berhasil dihapus.');
    }

    public function wizard(Request $request, Competition $competition): View
    {
        $competition = $this->competitionService->findForEvent($competition->id);
        $step = (int) $request->get('step', 1);

        return view('competitions.wizard', [
            'competition' => $competition,
            'step' => $step,
            'participants' => $this->participantService->getAllForEvent(),
        ]);
    }

    public function saveWizard(Request $request, Competition $competition): RedirectResponse
    {
        $competition = $this->competitionService->findForEvent($competition->id);
        $step = (int) $request->input('step', 1);

        $validated = $request->validate(match ($step) {
            2 => array_filter([
                'participant_ids' => ['required', 'array', 'min:2'],
                'participant_ids.*' => ['exists:participants,id'],
                'seeds' => ['nullable', 'array'],
                'players_per_side' => ['nullable', 'integer', 'min:1', 'max:10'],
                'group_count' => $competition->system === \App\Enums\CompetitionSystem::GroupKnockout
                    ? ['required', 'integer', 'min:2', 'max:16']
                    : ['nullable'],
                'qualify_per_group' => $competition->system === \App\Enums\CompetitionSystem::GroupKnockout
                    ? ['nullable', 'integer', 'min:1', 'max:4']
                    : ['nullable'],
            ]),
            3 => [
                'status' => ['nullable', 'string'],
                'generate_bracket' => ['nullable', 'boolean'],
                'config' => ['nullable', 'array'],
            ],
            default => [],
        });

        $this->competitionService->saveWizardStep($competition, $step, $validated);

        if ($step >= 3) {
            return redirect()->route('competitions.show', $competition)
                ->with('success', 'Wizard lomba selesai.');
        }

        return redirect()->route('competitions.wizard', ['competition' => $competition, 'step' => $step + 1])
            ->with('success', 'Langkah wizard berhasil disimpan.');
    }

    public function randomizeMatches(Competition $competition): RedirectResponse
    {
        $competition = $this->competitionService->findForEvent($competition->id);

        try {
            $this->matchService->randomizeMatchups($competition);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Peserta berhasil diacak ulang.');
    }
}
