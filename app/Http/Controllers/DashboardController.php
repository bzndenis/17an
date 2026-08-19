<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Services\EventService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected EventService $eventService,
    ) {}

    public function index(): View
    {
        $activeEvent = $this->eventService->getActiveEvent();

        if (! $activeEvent) {
            return view('dashboard', [
                'stats' => [],
                'recentActivity' => collect(),
                'upcomingMatches' => collect(),
                'competitionOverview' => collect(),
                'noEvent' => true,
            ]);
        }

        return view('dashboard', [
            'stats' => $this->dashboardService->getStats(),
            'recentActivity' => $this->dashboardService->getRecentActivity(),
            'upcomingMatches' => $this->dashboardService->getUpcomingMatches(),
            'competitionOverview' => $this->dashboardService->getCompetitionOverview(),
            'noEvent' => false,
        ]);
    }
}
