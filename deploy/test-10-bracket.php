<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Competition;
use App\Services\BracketService;

// Create temp competition with 10 participants
$event = \App\Models\Event::first();
$comp = Competition::create([
    'event_id' => $event->id,
    'name' => 'Test 10',
    'slug' => 'test-10',
    'system' => 'knockout',
    'category' => 'Test',
    'status' => 'draft',
]);

$participants = \App\Models\Participant::take(10)->get();
$seed = 1;
foreach ($participants as $p) {
    $comp->participants()->attach($p->id, ['seed' => $seed++]);
}

$bs = app(BracketService::class);
$bs->generateBracket($comp);

$rounds = $comp->rounds()->with('matches.matchParticipants.participant')->orderBy('round_number')->get();
echo "Competition: {$comp->name} ({$comp->participants()->count()} peserta)\n\n";

foreach ($rounds as $r) {
    echo "{$r->name} (R{$r->round_number}): {$r->matches->count()} matches\n";
    foreach ($r->matches as $m) {
        $names = $m->matchParticipants->map(fn($mp) => $mp->participant->name)->join(' vs ');
        echo "  #{$m->match_number}: {$names} [{$m->status->value}]\n";
    }
    echo "\n";
}

// Cleanup
$comp->matches()->delete();
$comp->rounds()->delete();
$comp->participants()->detach();
$comp->forceDelete();
