<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Collection;

class EventService
{
    public const SESSION_KEY = 'active_event_id';

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
}
