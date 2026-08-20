# Graph Report - 17an  (2026-08-20)

## Corpus Check
- 207 files · ~72,285 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 866 nodes · 1491 edges · 144 communities (128 shown, 16 thin omitted)
- Extraction: 95% EXTRACTED · 5% INFERRED · 0% AMBIGUOUS · INFERRED: 69 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `99ce8a31`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Illuminate\Http\RedirectResponse
- Competition
- User
- app.md
- composer.json
- Illuminate\Foundation\Http\FormRequest
- devDependencies
- scripts
- Illuminate\Database\Eloquent\Relations\BelongsTo
- EventService
- Event
- 17an Competition Dashboard — Design Spec
- 17an Competition Dashboard
- ParticipantController.php
- Participant
- Deploy 17an Dashboard ke Niagahoster
- Event.php
- .run
- Schedule
- Announcement
- GameMatch
- ParticipantsExport
- DashboardService
- Competition.php
- app.js
- SetActiveEvent.php
- AppServiceProvider.php
- UserFactory
- competitions/show.blade.php
- ExampleTest
- profile/edit.blade.php
- brackets/show.blade.php
- app.blade.php
- group-knockout.blade.php
- competition.blade.php
- global.blade.php
- post-deploy.sh
- visual.blade.php
- events/create.blade.php
- events/edit.blade.php

## God Nodes (most connected - your core abstractions)
1. `Competition` - 65 edges
2. `EventService` - 51 edges
3. `Controller` - 30 edges
4. `GameMatch` - 30 edges
5. `CompetitionService` - 30 edges
6. `Participant` - 29 edges
7. `ActivityLogService` - 27 edges
8. `User` - 26 edges
9. `BracketService` - 26 edges
10. `Event` - 25 edges

## Surprising Connections (you probably didn't know these)
- `AnnouncementController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/AnnouncementController.php → app/Http/Controllers/Controller.php
- `AnnouncementController` --references--> `ActivityLogService`  [EXTRACTED]
  app/Http/Controllers/AnnouncementController.php → app/Services/ActivityLogService.php
- `AnnouncementController` --references--> `EventService`  [EXTRACTED]
  app/Http/Controllers/AnnouncementController.php → app/Services/EventService.php
- `BracketController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/BracketController.php → app/Http/Controllers/Controller.php
- `CompetitionController` --references--> `CompetitionService`  [EXTRACTED]
  app/Http/Controllers/CompetitionController.php → app/Services/CompetitionService.php

## Import Cycles
- None detected.

## Communities (144 total, 16 thin omitted)

### Community 0 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.06
Nodes (20): AuthenticatedSessionController, ConfirmablePasswordController, EmailVerificationNotificationController, EmailVerificationPromptController, NewPasswordController, PasswordController, PasswordResetLinkController, RegisteredUserController (+12 more)

### Community 1 - "Competition"
Cohesion: 0.06
Nodes (13): BracketController, MatchController, RankingController, Competition, Ranking, Round, BracketService, CompetitionService (+5 more)

### Community 2 - "User"
Cohesion: 0.08
Nodes (15): User, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Foundation\Auth\User, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Notifications\Notifiable, AuthenticationTest, EmailVerificationTest (+7 more)

### Community 3 - "app.md"
Cohesion: 0.04
Nodes (44): 10. INPUT HASIL PERTANDINGAN, 11. DETAIL LOMBA, 12. HALAMAN PERTANDINGAN, 13. HALAMAN RANKING GLOBAL, 14. DATA MODEL, 15. RELATIONSHIP, 16. BUSINESS LOGIC, 17. UI/UX DESIGN SYSTEM (+36 more)

### Community 4 - "composer.json"
Cohesion: 0.05
Nodes (42): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+34 more)

### Community 5 - "Illuminate\Foundation\Http\FormRequest"
Cohesion: 0.07
Nodes (7): LoginRequest, ProfileUpdateRequest, StoreCompetitionRequest, StoreParticipantRequest, UpdateCompetitionRequest, UpdateParticipantRequest, Illuminate\Foundation\Http\FormRequest

### Community 6 - "devDependencies"
Cohesion: 0.06
Nodes (33): alpinejs, autoprefixer, chart.js, concurrently, @laravel/multiplex, laravel-vite-plugin, dependencies, chart.js (+25 more)

### Community 7 - "scripts"
Cohesion: 0.08
Nodes (26): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+18 more)

### Community 8 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.13
Nodes (5): ActivityLog, CompetitionParticipant, MatchParticipant, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 9 - "EventService"
Cohesion: 0.17
Nodes (3): SettingsController, ActivityLogService, EventService

### Community 11 - "17an Competition Dashboard — Design Spec"
Cohesion: 0.09
Nodes (22): 17an Competition Dashboard — Design Spec, Architecture, Business Flows, Constraints, Core Tables, Create Competition (5-step wizard), Database Schema, Decisions (+14 more)

### Community 12 - "17an Competition Dashboard"
Cohesion: 0.09
Nodes (22): 17an Competition Dashboard, 1. Database, 2. Environment, 3. Install & Migrate, 4. Akses, Bracket Turnamen, Dashboard, Development (+14 more)

### Community 13 - "ParticipantController.php"
Cohesion: 0.15
Nodes (6): ParticipantController, ParticipantsImport, ParticipantCategory, Maatwebsite\Excel\Concerns\ToCollection, Maatwebsite\Excel\Concerns\WithHeadingRow, Symfony\Component\HttpFoundation\BinaryFileResponse

### Community 14 - "Participant"
Cohesion: 0.18
Nodes (3): Participant, ParticipantService, Illuminate\Database\Eloquent\Collection

### Community 15 - "Deploy 17an Dashboard ke Niagahoster"
Cohesion: 0.11
Nodes (17): 1. Siapkan file di komputer (Laragon), 2. Upload project, 3. Buat database MySQL, 4. Atur Document Root, 5. Buat file `.env` di server, 6. Jalankan perintah (SSH), 7. Permission folder, Checklist deploy (+9 more)

### Community 16 - "Event.php"
Cohesion: 0.14
Nodes (3): EventSetting, Illuminate\Database\Eloquent\Relations\HasOne, Illuminate\Database\Eloquent\SoftDeletes

### Community 18 - ".run"
Cohesion: 0.15
Nodes (4): Award, MatchResult, DatabaseSeeder, Illuminate\Database\Seeder

### Community 22 - "ParticipantsExport"
Cohesion: 0.31
Nodes (4): ParticipantsExport, Maatwebsite\Excel\Concerns\FromCollection, Maatwebsite\Excel\Concerns\WithHeadings, Maatwebsite\Excel\Concerns\WithMapping

### Community 25 - "app.js"
Cohesion: 0.32
Nodes (3): add(), init(), remove()

### Community 26 - "SetActiveEvent.php"
Cohesion: 0.47
Nodes (3): SetActiveEvent, Closure, Symfony\Component\HttpFoundation\Response

### Community 28 - "UserFactory"
Cohesion: 0.47
Nodes (3): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, static

### Community 29 - "competitions/show.blade.php"
Cohesion: 0.33
Nodes (5): brackets.partials.group-knockout, brackets.partials.point-system, brackets.partials.visual, rankings.partials.podium, rankings.partials.table

### Community 31 - "profile/edit.blade.php"
Cohesion: 0.50
Nodes (3): profile.partials.delete-user-form, profile.partials.update-password-form, profile.partials.update-profile-information-form

### Community 32 - "brackets/show.blade.php"
Cohesion: 0.50
Nodes (3): brackets.partials.group-knockout, brackets.partials.point-system, brackets.partials.visual

## Knowledge Gaps
- **179 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+174 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **16 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Competition` connect `Competition` to `Illuminate\Http\RedirectResponse`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `Event`, `Event.php`, `DatabaseSeeder.php`, `.run`, `DashboardService`, `Competition.php`?**
  _High betweenness centrality (0.046) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `Illuminate\Http\RedirectResponse`, `.run`?**
  _High betweenness centrality (0.030) - this node is a cross-community bridge._
- **Why does `EventService` connect `EventService` to `Illuminate\Http\RedirectResponse`, `Competition`, `Event`, `ParticipantController.php`, `Participant`, `Event.php`, `Schedule`, `Announcement`, `DashboardService`, `SetActiveEvent.php`?**
  _High betweenness centrality (0.029) - this node is a cross-community bridge._
- **Are the 4 inferred relationships involving `Competition` (e.g. with `.index()` and `.getCompetitionOverview()`) actually correct?**
  _`Competition` has 4 INFERRED edges - model-reasoned connections that need verification._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _179 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Http\RedirectResponse` be split into smaller, more focused modules?**
  _Cohesion score 0.05906553041434029 - nodes in this community are weakly interconnected._
- **Should `Competition` be split into smaller, more focused modules?**
  _Cohesion score 0.06368011847463902 - nodes in this community are weakly interconnected._