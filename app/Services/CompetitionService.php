<?php

namespace App\Services;

use App\Enums\CompetitionStatus;
use App\Enums\MatchStatus;
use App\Models\Competition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CompetitionService
{
    public function __construct(
        protected EventService $eventService,
        protected ActivityLogService $activityLogService,
        protected BracketService $bracketService,
        protected PointSystemService $pointSystemService,
    ) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $eventId = $this->eventService->requireActiveEventId();

        $query = Competition::withCount('participants', 'matches')
            ->forEvent($eventId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['system'])) {
            $query->where('system', $filters['system']);
        }

        return $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();
    }

    public function findForEvent(int $id): Competition
    {
        $eventId = $this->eventService->requireActiveEventId();

        return Competition::with([
            'participants.category',
            'rounds.matches.matchParticipants.participant',
            'rankings.participant',
            'awards.participant',
        ])
            ->forEvent($eventId)
            ->findOrFail($id);
    }

    public function create(array $data): Competition
    {
        $eventId = $this->eventService->requireActiveEventId();
        $data['event_id'] = $eventId;
        $data['slug'] = $this->generateUniqueSlug($data['name'], $eventId);

        $competition = Competition::create($data);

        $this->activityLogService->log(
            'competition.created',
            "Lomba {$competition->name} dibuat.",
            ['competition_id' => $competition->id]
        );

        return $competition;
    }

    public function update(Competition $competition, array $data): Competition
    {
        $this->ensureBelongsToActiveEvent($competition);

        if (isset($data['name']) && $data['name'] !== $competition->name) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $competition->event_id, $competition->id);
        }

        $competition->update($data);

        $this->activityLogService->log(
            'competition.updated',
            "Lomba {$competition->name} diperbarui.",
            ['competition_id' => $competition->id]
        );

        return $competition->fresh();
    }

    public function delete(Competition $competition): void
    {
        $this->ensureBelongsToActiveEvent($competition);

        $this->activityLogService->log(
            'competition.deleted',
            "Lomba {$competition->name} dihapus.",
            ['competition_id' => $competition->id]
        );

        $competition->delete();
    }

    public function saveWizardStep(Competition $competition, int $step, array $data): Competition
    {
        $this->ensureBelongsToActiveEvent($competition);

        return match ($step) {
            1 => $this->update($competition, $data),
            2 => $this->saveWizardParticipants($competition, $data),
            3 => $this->finalizeWizard($competition, $data),
            default => $competition,
        };
    }

    protected function saveWizardParticipants(Competition $competition, array $data): Competition
    {
        if ($competition->system === \App\Enums\CompetitionSystem::GroupKnockout) {
            $competition = $this->update($competition, [
                'config' => array_merge($competition->config ?? [], [
                    'group_count' => max(2, (int) ($data['group_count'] ?? 2)),
                    'qualify_per_group' => max(1, (int) ($data['qualify_per_group'] ?? 2)),
                ]),
            ]);
        }

        $competition = $this->syncParticipants(
            $competition,
            $data['participant_ids'] ?? [],
            $data['seeds'] ?? []
        );

        if ($competition->system === \App\Enums\CompetitionSystem::GroupKnockout) {
            $groupCount = max(2, (int) ($competition->config['group_count'] ?? 2));
            $entries = $competition->competitionParticipants()->orderBy('seed')->get();
            $this->bracketService->assignParticipantsToGroups($entries, $groupCount);
        }

        return $competition->fresh(['participants', 'competitionParticipants']);
    }

    public function syncParticipants(Competition $competition, array $participantIds, array $seeds = []): Competition
    {
        $syncData = [];

        foreach ($participantIds as $index => $participantId) {
            $syncData[$participantId] = [
                'seed' => $seeds[$participantId] ?? ($index + 1),
            ];
        }

        $competition->participants()->sync($syncData);
        $this->pointSystemService->initializeRankings($competition);

        return $competition->fresh(['participants']);
    }

    public function getShowTabData(Competition $competition, string $tab = 'overview'): array
    {
        $this->ensureBelongsToActiveEvent($competition);

        return match ($tab) {
            'participants' => [
                'participants' => $competition->competitionParticipants()
                    ->with('participant.category')
                    ->orderBy('seed')
                    ->get(),
            ],
            'matches' => [
                'matches' => $competition->matches()
                    ->with(['round', 'matchParticipants.participant', 'result.winner'])
                    ->orderBy('match_number')
                    ->get(),
                'canRandomizeMatches' => app(MatchService::class)->canRandomizeMatchups($competition),
            ],
            'bracket' => (function () use ($competition) {
                $this->bracketService->fixRoundNames($competition);

                return [
                    'rounds' => $competition->rounds()
                        ->with(['matches.matchParticipants.participant', 'matches.result'])
                        ->orderBy('round_number')
                        ->get(),
                    'canRandomizeMatches' => app(MatchService::class)->canRandomizeMatchups($competition),
                    'groupEntries' => $competition->competitionParticipants()
                        ->with('participant')
                        ->whereNotNull('group_number')
                        ->orderBy('group_number')
                        ->orderBy('seed')
                        ->get()
                        ->groupBy('group_number'),
                ];
            })(),
            'ranking' => [
                'rankings' => $competition->rankings()
                    ->with('participant')
                    ->orderByDesc('points')
                    ->orderByDesc('won')
                    ->get(),
            ],
            default => [
                'stats' => [
                    'participants_count' => $competition->participants()->count(),
                    'matches_count' => $competition->matches()->count(),
                    'finished_matches' => $competition->matches()->where('status', MatchStatus::Finished)->count(),
                ],
            ],
        };
    }

    protected function finalizeWizard(Competition $competition, array $data): Competition
    {
        $competition->update([
            'status' => $data['status'] ?? CompetitionStatus::Registration,
            'config' => array_merge($competition->config ?? [], $data['config'] ?? []),
        ]);

        if ($data['generate_bracket'] ?? false) {
            $this->bracketService->generateBracket($competition);
        }

        return $competition->fresh();
    }

    protected function generateUniqueSlug(string $name, int $eventId, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 1;

        while (
            Competition::where('event_id', $eventId)
                ->where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $original.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    protected function ensureBelongsToActiveEvent(Competition $competition): void
    {
        $eventId = $this->eventService->requireActiveEventId();

        if ($competition->event_id !== $eventId) {
            abort(404);
        }
    }
}
