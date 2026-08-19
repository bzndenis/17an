<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\ParticipantCategory;
use App\Services\CompetitionService;
use App\Services\EventService;
use App\Services\RankingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RankingController extends Controller
{
    public function __construct(
        protected RankingService $rankingService,
        protected CompetitionService $competitionService,
        protected EventService $eventService,
    ) {}

    public function competition(Competition $competition): View
    {
        $competition = $this->competitionService->findForEvent($competition->id);
        $leaderboard = $this->rankingService->getLeaderboard($competition);

        return view('rankings.competition', compact('competition', 'leaderboard'));
    }

    public function global(Request $request): View
    {
        $eventId = $this->eventService->requireActiveEventId();
        $rankings = $this->rankingService->getGlobalRanking($request->only([
            'competition_id', 'category_id', 'gender', 'team',
        ]));

        return view('rankings.global', [
            'rankings' => $rankings,
            'chartData' => $this->rankingService->getChartData($rankings),
            'competitions' => Competition::forEvent($eventId)->orderBy('name')->get(),
            'categories' => ParticipantCategory::where('event_id', $eventId)->orderBy('name')->get(),
            'filters' => $request->only(['competition_id', 'category_id', 'gender', 'team']),
        ]);
    }
}
