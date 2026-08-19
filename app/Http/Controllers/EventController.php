<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(
        protected EventService $eventService,
    ) {}

    public function index(): View
    {
        return view('events.index', [
            'events' => $this->eventService->paginate(),
            'activeEventId' => $this->eventService->getActiveEventId(),
        ]);
    }

    public function create(): View
    {
        return view('events.create', [
            'statuses' => EventStatus::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:'.implode(',', array_column(EventStatus::cases(), 'value'))],
            'is_active' => ['nullable', 'boolean'],
            'venue_default' => ['nullable', 'string', 'max:255'],
            'theme_color' => ['nullable', 'string', 'max:20'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $event = $this->eventService->create($validated);

        if ($event->is_active) {
            $this->eventService->switchEvent($event->id);
        }

        return redirect()->route('events.index')
            ->with('success', "Event \"{$event->name}\" berhasil dibuat.");
    }

    public function edit(Event $event): View
    {
        $event->load('settings');

        return view('events.edit', [
            'event' => $event,
            'statuses' => EventStatus::cases(),
        ]);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:'.implode(',', array_column(EventStatus::cases(), 'value'))],
            'is_active' => ['nullable', 'boolean'],
            'venue_default' => ['nullable', 'string', 'max:255'],
            'theme_color' => ['nullable', 'string', 'max:20'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $this->eventService->update($event, $validated);

        return redirect()->route('events.index')
            ->with('success', "Event \"{$event->name}\" berhasil diperbarui.");
    }

    public function destroy(Event $event): RedirectResponse
    {
        if ($event->participants()->exists() || $event->competitions()->exists()) {
            return back()->with('error', 'Event tidak bisa dihapus karena masih memiliki peserta atau lomba.');
        }

        $name = $event->name;
        $this->eventService->delete($event);

        return redirect()->route('events.index')
            ->with('success', "Event \"{$name}\" berhasil dihapus.");
    }

    public function switch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
        ]);

        $event = $this->eventService->switchEvent((int) $validated['event_id']);

        return back()->with('success', "Event aktif diubah ke: {$event->name}");
    }
}
