<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    public function __construct(
        protected EventService $eventService,
    ) {}

    public function log(string $action, ?string $description = null, array $metadata = []): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'event_id' => $this->eventService->getActiveEventId(),
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata ?: null,
        ]);
    }
}
