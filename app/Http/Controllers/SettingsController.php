<?php

namespace App\Http\Controllers;

use App\Models\EventSetting;
use App\Services\ActivityLogService;
use App\Services\EventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        protected EventService $eventService,
        protected ActivityLogService $activityLogService,
    ) {}

    public function edit(): View
    {
        $event = $this->eventService->getActiveEvent();
        $settings = $event?->settings ?? new EventSetting([
            'theme_color' => '#D71920',
        ]);

        return view('settings.edit', compact('event', 'settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $event = $this->eventService->getActiveEvent();

        if (! $event) {
            return back()->with('error', 'Tidak ada event aktif.');
        }

        $validated = $request->validate([
            'logo' => ['nullable', 'image', 'max:2048'],
            'theme_color' => ['required', 'string', 'max:20'],
            'venue_default' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('events', 'public');
        } else {
            unset($validated['logo']);
        }

        EventSetting::updateOrCreate(
            ['event_id' => $event->id],
            $validated
        );

        $this->activityLogService->log(
            'settings.updated',
            'Pengaturan event diperbarui.',
            ['event_id' => $event->id]
        );

        return back()->with('success', 'Pengaturan event berhasil disimpan.');
    }
}
