<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Competition;
use App\Models\Participant;
use App\Services\BracketService;

$bs = app(BracketService::class);
$event = \App\Models\Event::where('is_active', true)->first();
$allParticipants = Participant::where('event_id', $event->id)->get();
echo "Event: {$event->name}, Participants: {$allParticipants->count()}\n\n";

$testCounts = [2, 3, 4, 5, 7, 8, 10, 16];

foreach ($testCounts as $count) {
    $name = "TestKO-{$count}";
    $comp = Competition::create([
        'event_id' => $event->id,
        'name' => $name,
        'slug' => strtolower($name),
        'system' => 'knockout',
        'category' => 'Test',
        'status' => 'draft',
    ]);

    $participants = $allParticipants->take($count);
    $seed = 1;
    foreach ($participants as $p) {
        $comp->participants()->attach($p->id, ['seed' => $seed++]);
    }

    try {
        $bs->generateBracket($comp);

        $rounds = $comp->rounds()->with('matches.matchParticipants.participant')->orderBy('round_number')->get();
        $totalMatches = $rounds->sum(fn($r) => $r->matches->count());

        echo "=== {$count} peserta → {$rounds->count()} rounds, {$totalMatches} matches ===\n";

        foreach ($rounds as $r) {
            echo "  {$r->name} (R{$r->round_number}): {$r->matches->count()} matches\n";
            foreach ($r->matches as $m) {
                $names = $m->matchParticipants->map(fn($mp) => $mp->participant->name . ($mp->is_winner ? '(W)' : ''))->join(' vs ');
                $next = $m->next_match_id ? "→#{$m->next_match_id}" : '';
                echo "    #{$m->match_number}: {$names} [{$m->status->value}] {$next}\n";
            }
        }

        // Validate: every non-final match must have next_match_id
        $missingNext = $comp->matches()
            ->whereNull('next_match_id')
            ->whereHas('round', fn($q) => $q->where('round_number', '<', $rounds->max('round_number')))
            ->count();
        if ($missingNext > 0) {
            echo "  ⚠ WARNING: {$missingNext} non-final matches missing next_match_id!\n";
        }

        echo "\n";
    } catch (\Throwable $e) {
        echo "=== {$count} peserta → ERROR: {$e->getMessage()} ===\n\n";
    }

    // Cleanup
    $bs->resetBracket($comp);
    $comp->participants()->detach();
    $comp->forceDelete();
}

// Test Point system
echo "=== POINT SYSTEM: 6 peserta ===\n";
$comp2 = Competition::create([
    'event_id' => $event->id,
    'name' => 'TestPoint-6',
    'slug' => 'testpoint-6',
    'system' => 'point',
    'category' => 'Test',
    'status' => 'draft',
]);
$seed = 1;
foreach ($allParticipants->take(6) as $p) {
    $comp2->participants()->attach($p->id, ['seed' => $seed++]);
}
$bs->generateBracket($comp2);
$rounds = $comp2->rounds()->with('matches.matchParticipants.participant')->orderBy('round_number')->get();
$totalMatches = $rounds->sum(fn($r) => $r->matches->count());
echo "  {$rounds->count()} round, {$totalMatches} matches (expected: C(6,2) = 15)\n";
foreach ($rounds as $r) {
    echo "  {$r->name}: {$r->matches->count()} matches\n";
    foreach ($r->matches->take(3) as $m) {
        $names = $m->matchParticipants->map(fn($mp) => $mp->participant->name)->join(' vs ');
        echo "    #{$m->match_number}: {$names}\n";
    }
    if ($r->matches->count() > 3) echo "    ... and " . ($r->matches->count() - 3) . " more\n";
}
$bs->resetBracket($comp2);
$comp2->participants()->detach();
$comp2->forceDelete();
