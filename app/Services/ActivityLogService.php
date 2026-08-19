<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    public function log(string $action, ?string $description = null, array $metadata = [], ?int $eventId = null): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'event_id' => $eventId ?? session(EventService::SESSION_KEY),
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata ?: null,
        ]);
    }
}
