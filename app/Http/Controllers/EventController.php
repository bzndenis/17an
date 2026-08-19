<?php

namespace App\Http\Controllers;

use App\Services\EventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(
        protected EventService $eventService,
    ) {}

    public function switch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
        ]);

        $event = $this->eventService->switchEvent((int) $validated['event_id']);

        return back()->with('success', "Event aktif diubah ke: {$event->name}");
    }
}
