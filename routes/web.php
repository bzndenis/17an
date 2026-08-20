<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\BracketController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified', 'event'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/events/switch', [EventController::class, 'switch'])->name('events.switch');
    Route::resource('events', EventController::class)->except(['show']);

    Route::resource('participants', ParticipantController::class);
    Route::get('participants-export', [ParticipantController::class, 'export'])->name('participants.export');
    Route::post('participants-import', [ParticipantController::class, 'import'])->name('participants.import');

    Route::resource('competitions', CompetitionController::class);
    Route::get('competitions/{competition}/wizard', [CompetitionController::class, 'wizard'])->name('competitions.wizard');
    Route::post('competitions/{competition}/wizard', [CompetitionController::class, 'saveWizard'])->name('competitions.wizard.save');
    Route::post('competitions/{competition}/matches/randomize', [CompetitionController::class, 'randomizeMatches'])->name('competitions.matches.randomize');

    Route::get('matches', [MatchController::class, 'index'])->name('matches.index');
    Route::get('matches/{match}', [MatchController::class, 'show'])->name('matches.show');
    Route::post('matches/{match}/result', [MatchController::class, 'storeResult'])->name('matches.result');

    Route::get('competitions/{competition}/bracket', [BracketController::class, 'show'])->name('brackets.show');
    Route::post('competitions/{competition}/bracket/generate', [BracketController::class, 'generate'])->name('brackets.generate');
    Route::post('competitions/{competition}/bracket/randomize', [BracketController::class, 'randomize'])->name('brackets.randomize');
    Route::put('competitions/{competition}/bracket', [BracketController::class, 'update'])->name('brackets.update');

    Route::get('competitions/{competition}/ranking', [RankingController::class, 'competition'])->name('rankings.competition');
    Route::get('rankings', [RankingController::class, 'global'])->name('rankings.global');

    Route::resource('schedules', ScheduleController::class)->except(['show']);
    Route::resource('announcements', AnnouncementController::class)->except(['show']);

    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
