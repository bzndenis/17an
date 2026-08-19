<?php

namespace App\Services;

use App\Models\Participant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ParticipantService
{
    public function __construct(
        protected EventService $eventService,
        protected ActivityLogService $activityLogService,
    ) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $eventId = $this->eventService->requireActiveEventId();

        $query = Participant::with('category')
            ->forEvent($eventId);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('number', 'like', "%{$search}%")
                    ->orWhere('team', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $sortBy = $filters['sort_by'] ?? 'number';
        $sortDir = $filters['sort_dir'] ?? 'asc';
        $allowedSorts = ['name', 'number', 'created_at', 'status'];

        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function findForEvent(int $id): Participant
    {
        $eventId = $this->eventService->requireActiveEventId();

        return Participant::with(['category', 'competitions'])
            ->forEvent($eventId)
            ->findOrFail($id);
    }

    public function create(array $data): Participant
    {
        $eventId = $this->eventService->requireActiveEventId();
        $data['event_id'] = $eventId;

        $participant = Participant::create($data);

        $this->activityLogService->log(
            'participant.created',
            "Peserta {$participant->name} ditambahkan.",
            ['participant_id' => $participant->id]
        );

        return $participant;
    }

    public function update(Participant $participant, array $data): Participant
    {
        $this->ensureBelongsToActiveEvent($participant);
        $participant->update($data);

        $this->activityLogService->log(
            'participant.updated',
            "Data peserta {$participant->name} diperbarui.",
            ['participant_id' => $participant->id]
        );

        return $participant->fresh();
    }

    public function delete(Participant $participant): void
    {
        $this->ensureBelongsToActiveEvent($participant);

        $this->activityLogService->log(
            'participant.deleted',
            "Peserta {$participant->name} dihapus.",
            ['participant_id' => $participant->id]
        );

        $participant->delete();
    }

    public function getAllForEvent(): Collection
    {
        $eventId = $this->eventService->requireActiveEventId();

        return Participant::forEvent($eventId)
            ->orderBy('number')
            ->get();
    }

    protected function ensureBelongsToActiveEvent(Participant $participant): void
    {
        $eventId = $this->eventService->requireActiveEventId();

        if ($participant->event_id !== $eventId) {
            abort(404);
        }
    }
}
