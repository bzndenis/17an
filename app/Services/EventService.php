<?php

namespace App\Services;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class EventService
{
    public const SESSION_KEY = 'active_event_id';

    public function __construct(
        protected ActivityLogService $activityLogService,
    ) {}

    public function getActiveEventId(): ?int
    {
        return session(self::SESSION_KEY);
    }

    public function getActiveEvent(): ?Event
    {
        $eventId = $this->getActiveEventId();

        if ($eventId) {
            $event = Event::with('settings')->find($eventId);

            if ($event) {
                return $event;
            }
        }

        $event = Event::with('settings')
            ->where('is_active', true)
            ->orderByDesc('year')
            ->first();

        if (! $event) {
            $event = Event::with('settings')->orderByDesc('year')->first();
        }

        if ($event) {
            session([self::SESSION_KEY => $event->id]);
        }

        return $event;
    }

    public function switchEvent(int $eventId): Event
    {
        $event = Event::with('settings')->findOrFail($eventId);
        session([self::SESSION_KEY => $event->id]);

        return $event;
    }

    public function getAllEvents(): Collection
    {
        return Event::orderByDesc('year')->get();
    }

    public function requireActiveEventId(): int
    {
        $event = $this->getActiveEvent();

        if (! $event) {
            abort(404, 'Tidak ada event aktif. Silakan buat event terlebih dahulu.');
        }

        return $event->id;
    }

    public function paginate(int $perPage = 10)
    {
        return Event::withCount(['participants', 'competitions'])
            ->orderByDesc('year')
            ->orderByDesc('start_date')
            ->paginate($perPage);
    }

    public function find(int $id): Event
    {
        return Event::with('settings')->findOrFail($id);
    }

    public function create(array $data): Event
    {
        if ($data['is_active'] ?? false) {
            Event::where('is_active', true)->update(['is_active' => false]);
        }

        $event = Event::create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
            'year' => $data['year'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => $data['status'] ?? EventStatus::Draft->value,
            'is_active' => $data['is_active'] ?? false,
        ]);

        EventSetting::create([
            'event_id' => $event->id,
            'theme_color' => $data['theme_color'] ?? '#D71920',
            'venue_default' => $data['venue_default'] ?? null,
        ]);

        $this->activityLogService->log('event.created', "Event {$event->name} dibuat.", ['event_id' => $event->id], $event->id);

        return $event->load('settings');
    }

    public function update(Event $event, array $data): Event
    {
        if ($data['is_active'] ?? false) {
            Event::where('id', '!=', $event->id)->where('is_active', true)->update(['is_active' => false]);
        }

        $event->update([
            'name' => $data['name'],
            'slug' => $event->slug ?: $this->uniqueSlug($data['name'], $event->id),
            'year' => $data['year'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => $data['status'],
            'is_active' => $data['is_active'] ?? false,
        ]);

        if (isset($data['theme_color']) || isset($data['venue_default'])) {
            EventSetting::updateOrCreate(
                ['event_id' => $event->id],
                array_filter([
                    'theme_color' => $data['theme_color'] ?? null,
                    'venue_default' => $data['venue_default'] ?? null,
                ])
            );
        }

        if ($event->is_active) {
            session([self::SESSION_KEY => $event->id]);
        }

        $this->activityLogService->log('event.updated', "Event {$event->name} diperbarui.", ['event_id' => $event->id], $event->id);

        return $event->fresh('settings');
    }

    public function delete(Event $event): void
    {
        if ($this->getActiveEventId() === $event->id) {
            session()->forget(self::SESSION_KEY);
        }

        $this->activityLogService->log('event.deleted', "Event {$event->name} dihapus.", ['event_id' => $event->id], $event->id);

        $event->delete();
    }

    protected function uniqueSlug(string $name, ?int $exceptId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 1;

        while (
            Event::where('slug', $slug)
                ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
                ->exists()
        ) {
            $slug = $original.'-'.$counter++;
        }

        return $slug;
    }
}
