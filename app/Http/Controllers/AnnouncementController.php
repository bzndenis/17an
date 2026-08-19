<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Services\ActivityLogService;
use App\Services\EventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function __construct(
        protected EventService $eventService,
        protected ActivityLogService $activityLogService,
    ) {}

    public function index(): View
    {
        $eventId = $this->eventService->requireActiveEventId();

        return view('announcements.index', [
            'announcements' => Announcement::forEvent($eventId)->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('announcements.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $eventId = $this->eventService->requireActiveEventId();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'published_at' => ['nullable', 'date'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $announcement = Announcement::create(array_merge($validated, [
            'event_id' => $eventId,
            'is_published' => $request->boolean('is_published'),
            'published_at' => $request->boolean('is_published') ? ($validated['published_at'] ?? now()) : null,
        ]));

        $this->activityLogService->log(
            'announcement.created',
            "Pengumuman {$announcement->title} ditambahkan.",
            ['announcement_id' => $announcement->id]
        );

        return redirect()->route('announcements.index')
            ->with('success', 'Pengumuman berhasil ditambahkan.');
    }

    public function edit(Announcement $announcement): View
    {
        $this->ensureBelongsToActiveEvent($announcement);

        return view('announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->ensureBelongsToActiveEvent($announcement);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'published_at' => ['nullable', 'date'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $announcement->update(array_merge($validated, [
            'is_published' => $request->boolean('is_published'),
            'published_at' => $request->boolean('is_published')
                ? ($validated['published_at'] ?? $announcement->published_at ?? now())
                : null,
        ]));

        $this->activityLogService->log(
            'announcement.updated',
            "Pengumuman {$announcement->title} diperbarui.",
            ['announcement_id' => $announcement->id]
        );

        return redirect()->route('announcements.index')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->ensureBelongsToActiveEvent($announcement);

        $announcement->delete();

        $this->activityLogService->log(
            'announcement.deleted',
            "Pengumuman {$announcement->title} dihapus.",
            ['announcement_id' => $announcement->id]
        );

        return redirect()->route('announcements.index')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }

    protected function ensureBelongsToActiveEvent(Announcement $announcement): void
    {
        $eventId = $this->eventService->requireActiveEventId();

        if ($announcement->event_id !== $eventId) {
            abort(404);
        }
    }
}
