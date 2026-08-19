<?php

namespace App\Http\Middleware;

use App\Services\EventService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetActiveEvent
{
    public function __construct(
        protected EventService $eventService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $activeEvent = $this->eventService->getActiveEvent();
        $events = $this->eventService->getAllEvents();

        $request->attributes->set('active_event', $activeEvent);
        $request->attributes->set('events', $events);

        View::share('activeEvent', $activeEvent);
        View::share('events', $events);

        return $next($request);
    }
}
