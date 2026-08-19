<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$c = \App\Models\Competition::where('name', 'Panjat Pinang')->first();
echo "Competition: {$c->name} ({$c->participants()->count()} peserta)\n";

$rounds = $c->rounds()->with('matches.matchParticipants.participant')->orderBy('round_number')->get();
foreach ($rounds as $r) {
    echo "{$r->name} (R{$r->round_number}): {$r->matches->count()} matches\n";
    foreach ($r->matches as $m) {
        $names = $m->matchParticipants->map(fn($mp) => $mp->participant->name)->join(' vs ');
        echo "  #{$m->match_number}: {$names} [{$m->status->value}]\n";
    }
}
