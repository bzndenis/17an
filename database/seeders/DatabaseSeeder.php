<?php

namespace Database\Seeders;

use App\Enums\CompetitionStatus;
use App\Enums\CompetitionSystem;
use App\Enums\EventStatus;
use App\Enums\MatchStatus;
use App\Enums\ParticipantStatus;
use App\Models\Announcement;
use App\Models\Award;
use App\Models\Competition;
use App\Models\Event;
use App\Models\EventSetting;
use App\Models\GameMatch;
use App\Models\MatchParticipant;
use App\Models\MatchResult;
use App\Models\Participant;
use App\Models\ParticipantCategory;
use App\Models\Ranking;
use App\Models\Schedule;
use App\Models\User;
use App\Services\BracketService;
use App\Services\PointSystemService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@17an.test'],
            [
                'name' => 'Admin 17an',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $event2025 = Event::create([
            'name' => 'Festival 17 Agustus 2025',
            'slug' => 'festival-17-agustus-2025',
            'year' => 2025,
            'start_date' => '2025-08-16',
            'end_date' => '2025-08-17',
            'status' => EventStatus::Completed,
            'is_active' => false,
        ]);

        $event2026 = Event::create([
            'name' => 'Festival 17 Agustus 2026',
            'slug' => 'festival-17-agustus-2026',
            'year' => 2026,
            'start_date' => '2026-08-16',
            'end_date' => '2026-08-17',
            'status' => EventStatus::Active,
            'is_active' => true,
        ]);

        foreach ([$event2025, $event2026] as $event) {
            EventSetting::create([
                'event_id' => $event->id,
                'theme_color' => '#D71920',
                'venue_default' => 'Lapangan Utama RT 05',
            ]);
        }

        $activeEvent = $event2026;

        $categories = collect([
            'Anak-anak',
            'Remaja',
            'Dewasa',
            'Lansia',
        ])->map(fn (string $name) => ParticipantCategory::create([
            'event_id' => $activeEvent->id,
            'name' => $name,
        ]));

        $teams = ['Merah Putih', 'Garuda', 'Pancasila', 'Nusantara'];
        $maleNames = ['Andi', 'Budi', 'Candra', 'Doni', 'Eko', 'Fajar', 'Gilang', 'Hendra', 'Iwan', 'Joko'];
        $femaleNames = ['Ani', 'Bella', 'Citra', 'Dewi', 'Eka', 'Fitri', 'Gita', 'Hani', 'Indah', 'Julia'];

        $participants = collect();

        for ($i = 1; $i <= 10; $i++) {
            $participants->push(Participant::create([
                'event_id' => $activeEvent->id,
                'category_id' => $categories[$i <= 3 ? 0 : ($i <= 6 ? 1 : 2)]->id,
                'name' => $maleNames[$i - 1],
                'number' => $i,
                'gender' => 'male',
                'dob' => now()->subYears(20 + $i)->format('Y-m-d'),
                'phone' => '0812'.str_pad((string) $i, 8, '0', STR_PAD_LEFT),
                'address' => "Jl. Merdeka No. {$i}",
                'rt_rw' => '05/03',
                'team' => $teams[$i % 4],
                'status' => ParticipantStatus::Active,
            ]));
        }

        for ($i = 1; $i <= 10; $i++) {
            $participants->push(Participant::create([
                'event_id' => $activeEvent->id,
                'category_id' => $categories[$i <= 3 ? 0 : ($i <= 6 ? 1 : 2)]->id,
                'name' => $femaleNames[$i - 1],
                'number' => 10 + $i,
                'gender' => 'female',
                'dob' => now()->subYears(18 + $i)->format('Y-m-d'),
                'phone' => '0813'.str_pad((string) $i, 8, '0', STR_PAD_LEFT),
                'address' => "Jl. Kemerdekaan No. {$i}",
                'rt_rw' => '05/04',
                'team' => $teams[($i + 1) % 4],
                'status' => ParticipantStatus::Active,
            ]));
        }

        $competitionDefinitions = [
            [
                'name' => 'Balap Karung',
                'slug' => 'balap-karung',
                'system' => CompetitionSystem::Knockout,
                'category' => 'Olahraga Tradisional',
                'participant_indices' => range(0, 7),
            ],
            [
                'name' => 'Tarik Tambang',
                'slug' => 'tarik-tambang',
                'system' => CompetitionSystem::Knockout,
                'category' => 'Olahraga Tradisional',
                'participant_indices' => range(4, 11),
            ],
            [
                'name' => 'Panjat Pinang',
                'slug' => 'panjat-pinang',
                'system' => CompetitionSystem::Knockout,
                'category' => 'Olahraga Tradisional',
                'participant_indices' => range(0, 15),
            ],
            [
                'name' => 'Balap Bakiak',
                'slug' => 'balap-bakiak',
                'system' => CompetitionSystem::Point,
                'category' => 'Olahraga Tradisional',
                'participant_indices' => range(0, 5),
            ],
            [
                'name' => 'Makan Kerupuk',
                'slug' => 'makan-kerupuk',
                'system' => CompetitionSystem::Point,
                'category' => 'Hiburan',
                'participant_indices' => range(6, 11),
            ],
        ];

        $competitions = collect();

        foreach ($competitionDefinitions as $definition) {
            $indices = $definition['participant_indices'];
            unset($definition['participant_indices']);

            $competition = Competition::create(array_merge($definition, [
                'event_id' => $activeEvent->id,
                'description' => "Lomba {$definition['name']} dalam rangka perayaan 17 Agustus.",
                'status' => CompetitionStatus::Ongoing,
                'location' => 'Lapangan Utama RT 05',
                'start_at' => '2026-08-17 08:00:00',
                'duration_minutes' => 60,
                'prize_1' => 'Rp 500.000',
                'prize_2' => 'Rp 300.000',
                'prize_3' => 'Rp 150.000',
                'config' => ['win_points' => 3, 'draw_points' => 1, 'loss_points' => 0],
            ]));

            $syncData = [];
            foreach ($indices as $order => $index) {
                $syncData[$participants[$index]->id] = ['seed' => $order + 1];
            }

            $competition->participants()->sync($syncData);
            $competitions->push($competition);
        }

        $bracketService = app(BracketService::class);
        $pointSystemService = app(PointSystemService::class);

        foreach ($competitions as $competition) {
            $pointSystemService->initializeRankings($competition);
            $bracketService->generateBracket($competition);
        }

        $balapKarung = $competitions[0];
        $firstRoundMatches = GameMatch::where('competition_id', $balapKarung->id)
            ->whereHas('round', fn ($q) => $q->where('round_number', 1))
            ->with('matchParticipants')
            ->get();

        foreach ($firstRoundMatches->take(2) as $match) {
            $matchParticipants = $match->matchParticipants;

            if ($matchParticipants->count() < 2) {
                continue;
            }

            $winner = $matchParticipants->first();
            $loser = $matchParticipants->last();

            $winner->update(['score' => 1, 'is_winner' => true]);
            $loser->update(['score' => 0, 'is_winner' => false]);

            MatchResult::create([
                'match_id' => $match->id,
                'winner_id' => $winner->participant_id,
                'notes' => 'Pertandingan babak pertama selesai.',
                'finished_at' => now()->subHours(2),
            ]);

            $match->update(['status' => MatchStatus::Finished]);
            $bracketService->advanceWinner($match, $winner->participant_id);
        }

        $bakiak = $competitions[3];
        $bakiakMatches = GameMatch::where('competition_id', $bakiak->id)
            ->with('matchParticipants')
            ->take(3)
            ->get();

        foreach ($bakiakMatches as $match) {
            $mps = $match->matchParticipants;

            if ($mps->count() < 2) {
                continue;
            }

            $mps[0]->update(['score' => 2, 'is_winner' => true]);
            $mps[1]->update(['score' => 1, 'is_winner' => false]);

            MatchResult::create([
                'match_id' => $match->id,
                'winner_id' => $mps[0]->participant_id,
                'finished_at' => now()->subHour(),
            ]);

            $match->update(['status' => MatchStatus::Finished]);
            $pointSystemService->updateRanking($match);
        }

        Award::create([
            'competition_id' => $balapKarung->id,
            'participant_id' => $participants[0]->id,
            'position' => 1,
            'prize' => 'Rp 500.000',
        ]);

        Schedule::create([
            'event_id' => $activeEvent->id,
            'title' => 'Upacara Bendera',
            'description' => 'Upacara bendera memperingati HUT RI ke-81.',
            'datetime' => '2026-08-17 07:00:00',
            'location' => 'Lapangan Utama RT 05',
        ]);

        Schedule::create([
            'event_id' => $activeEvent->id,
            'title' => 'Pembukaan Lomba',
            'description' => 'Pembukaan seluruh rangkaian lomba 17 Agustus.',
            'datetime' => '2026-08-17 08:00:00',
            'location' => 'Lapangan Utama RT 05',
        ]);

        Announcement::create([
            'event_id' => $activeEvent->id,
            'title' => 'Selamat Hari Kemerdekaan!',
            'content' => 'Selamat merayakan HUT Kemerdekaan RI ke-81. Mari ikuti seluruh rangkaian lomba dengan sportivitas.',
            'published_at' => now(),
            'is_published' => true,
        ]);

        \App\Models\ActivityLog::create([
            'user_id' => $admin->id,
            'event_id' => $activeEvent->id,
            'action' => 'seeder.completed',
            'description' => 'Data dummy berhasil di-seed.',
            'metadata' => ['events' => 2, 'participants' => 20, 'competitions' => 5],
        ]);
    }
}
