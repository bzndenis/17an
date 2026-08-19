<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$event = \App\Models\Event::first();
echo "Event: {$event->name}\n";

$p = \App\Models\Participant::where('event_id', $event->id)->count();
echo "Participants in event: {$p}\n";

$comp = \App\Models\Competition::where('event_id', $event->id)->first();
if ($comp) {
    echo "Competition: {$comp->name} (system: {$comp->system->value})\n";
    echo "Participants via relationship: {$comp->participants()->count()}\n";
    echo "CompetitionParticipants: ".\App\Models\CompetitionParticipant::where('competition_id', $comp->id)->count()."\n";
}

// Test creating new comp
$newComp = \App\Models\Competition::create([
    'event_id' => $event->id,
    'name' => 'Debug',
    'slug' => 'debug-test',
    'system' => 'knockout',
    'category' => 'Test',
    'status' => 'draft',
]);

$participants = \App\Models\Participant::where('event_id', $event->id)->take(4)->get();
echo "\nAttaching {$participants->count()} participants...\n";

foreach ($participants as $idx => $pp) {
    $newComp->participants()->attach($pp->id, ['seed' => $idx + 1]);
}

echo "After attach: {$newComp->participants()->count()}\n";
echo "CP count: ".\App\Models\CompetitionParticipant::where('competition_id', $newComp->id)->count()."\n";

$newComp->participants()->detach();
$newComp->forceDelete();
