<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Services\ActivityLogService;
use App\Services\EventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function __construct(
        protected EventService $eventService,
        protected ActivityLogService $activityLogService,
    ) {}

    public function index(): View
    {
        $eventId = $this->eventService->requireActiveEventId();

        return view('schedules.index', [
            'schedules' => Schedule::forEvent($eventId)->orderBy('datetime')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('schedules.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $eventId = $this->eventService->requireActiveEventId();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'datetime' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        $schedule = Schedule::create(array_merge($validated, ['event_id' => $eventId]));

        $this->activityLogService->log(
            'schedule.created',
            "Jadwal {$schedule->title} ditambahkan.",
            ['schedule_id' => $schedule->id]
        );

        return redirect()->route('schedules.index')
            ->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function edit(Schedule $schedule): View
    {
        $this->ensureBelongsToActiveEvent($schedule);

        return view('schedules.edit', compact('schedule'));
    }

    public function update(Request $request, Schedule $schedule): RedirectResponse
    {
        $this->ensureBelongsToActiveEvent($schedule);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'datetime' => ['required', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        $schedule->update($validated);

        $this->activityLogService->log(
            'schedule.updated',
            "Jadwal {$schedule->title} diperbarui.",
            ['schedule_id' => $schedule->id]
        );

        return redirect()->route('schedules.index')
            ->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Schedule $schedule): RedirectResponse
    {
        $this->ensureBelongsToActiveEvent($schedule);

        $schedule->delete();

        $this->activityLogService->log(
            'schedule.deleted',
            "Jadwal {$schedule->title} dihapus.",
            ['schedule_id' => $schedule->id]
        );

        return redirect()->route('schedules.index')
            ->with('success', 'Jadwal berhasil dihapus.');
    }

    protected function ensureBelongsToActiveEvent(Schedule $schedule): void
    {
        $eventId = $this->eventService->requireActiveEventId();

        if ($schedule->event_id !== $eventId) {
            abort(404);
        }
    }
}
