# Graph Report - 17an  (2026-08-21)

## Corpus Check
- 208 files · ~73,417 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 876 nodes · 1516 edges · 140 communities (125 shown, 15 thin omitted)
- Extraction: 96% EXTRACTED · 4% INFERRED · 0% AMBIGUOUS · INFERRED: 68 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `95253b5f`
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
- .run
- Schedule
- GameMatch
- ParticipantsExport
- Competition.php
- app.js
- AppServiceProvider.php
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
1. `Competition` - 68 edges
2. `EventService` - 51 edges
3. `GameMatch` - 32 edges
4. `BracketService` - 31 edges
5. `Controller` - 30 edges
6. `CompetitionService` - 30 edges
7. `Participant` - 29 edges
8. `ActivityLogService` - 27 edges
9. `User` - 26 edges
10. `Event` - 25 edges

## Surprising Connections (you probably didn't know these)
- `AnnouncementController` --references--> `ActivityLogService`  [EXTRACTED]
  app/Http/Controllers/AnnouncementController.php → app/Services/ActivityLogService.php
- `AnnouncementController` --references--> `EventService`  [EXTRACTED]
  app/Http/Controllers/AnnouncementController.php → app/Services/EventService.php
- `BracketController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/BracketController.php → app/Http/Controllers/Controller.php
- `BracketController` --references--> `MatchService`  [EXTRACTED]
  app/Http/Controllers/BracketController.php → app/Services/MatchService.php
- `CompetitionController` --references--> `CompetitionService`  [EXTRACTED]
  app/Http/Controllers/CompetitionController.php → app/Services/CompetitionService.php

## Import Cycles
- None detected.

## Communities (140 total, 15 thin omitted)

### Community 0 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.05
Nodes (23): AnnouncementController, AuthenticatedSessionController, ConfirmablePasswordController, EmailVerificationNotificationController, EmailVerificationPromptController, NewPasswordController, PasswordController, PasswordResetLinkController (+15 more)

### Community 1 - "Competition"
Cohesion: 0.06
Nodes (11): BracketController, RankingController, Competition, Ranking, Round, BracketService, CompetitionService, PointSystemService (+3 more)

### Community 2 - "User"
Cohesion: 0.07
Nodes (18): User, UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Foundation\Auth\User, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, Illuminate\Notifications\Notifiable (+10 more)

### Community 3 - "app.md"
Cohesion: 0.04
Nodes (44): 10. INPUT HASIL PERTANDINGAN, 11. DETAIL LOMBA, 12. HALAMAN PERTANDINGAN, 13. HALAMAN RANKING GLOBAL, 14. DATA MODEL, 15. RELATIONSHIP, 16. BUSINESS LOGIC, 17. UI/UX DESIGN SYSTEM (+36 more)

### Community 4 - "composer.json"
Cohesion: 0.05
Nodes (42): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+34 more)

### Community 5 - "Illuminate\Foundation\Http\FormRequest"
Cohesion: 0.06
Nodes (7): LoginRequest, ProfileUpdateRequest, StoreCompetitionRequest, StoreParticipantRequest, UpdateCompetitionRequest, UpdateParticipantRequest, Illuminate\Foundation\Http\FormRequest

### Community 6 - "devDependencies"
Cohesion: 0.06
Nodes (33): alpinejs, autoprefixer, chart.js, concurrently, @laravel/multiplex, laravel-vite-plugin, dependencies, chart.js (+25 more)

### Community 7 - "scripts"
Cohesion: 0.08
Nodes (26): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+18 more)

### Community 8 - "Illuminate\Database\Eloquent\Relations\BelongsTo"
Cohesion: 0.15
Nodes (4): ActivityLog, CompetitionParticipant, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\Relations\BelongsTo

### Community 9 - "EventService"
Cohesion: 0.05
Nodes (18): DashboardController, ParticipantController, SettingsController, SetActiveEvent, ParticipantsImport, Participant, ParticipantCategory, ActivityLogService (+10 more)

### Community 11 - "17an Competition Dashboard — Design Spec"
Cohesion: 0.09
Nodes (22): 17an Competition Dashboard — Design Spec, Architecture, Business Flows, Constraints, Core Tables, Create Competition (5-step wizard), Database Schema, Decisions (+14 more)

### Community 12 - "17an Competition Dashboard"
Cohesion: 0.09
Nodes (22): 17an Competition Dashboard, 1. Database, 2. Environment, 3. Install & Migrate, 4. Akses, Bracket Turnamen, Dashboard, Development (+14 more)

### Community 15 - "Deploy 17an Dashboard ke Niagahoster"
Cohesion: 0.11
Nodes (17): 1. Siapkan file di komputer (Laragon), 2. Upload project, 3. Buat database MySQL, 4. Atur Document Root, 5. Buat file `.env` di server, 6. Jalankan perintah (SSH), 7. Permission folder, Checklist deploy (+9 more)

### Community 22 - "ParticipantsExport"
Cohesion: 0.31
Nodes (4): ParticipantsExport, Maatwebsite\Excel\Concerns\FromCollection, Maatwebsite\Excel\Concerns\WithHeadings, Maatwebsite\Excel\Concerns\WithMapping

### Community 24 - "Competition.php"
Cohesion: 0.19
Nodes (4): DatabaseSeeder, Illuminate\Database\Eloquent\Relations\BelongsToMany, Illuminate\Database\Eloquent\SoftDeletes, Illuminate\Database\Seeder

### Community 25 - "app.js"
Cohesion: 0.32
Nodes (3): add(), init(), remove()

### Community 27 - "AppServiceProvider.php"
Cohesion: 0.22
Nodes (3): AppServiceProvider, Illuminate\Database\Eloquent\Relations\HasOne, Illuminate\Support\ServiceProvider

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
- **15 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Competition` connect `Competition` to `Illuminate\Http\RedirectResponse`, `Illuminate\Database\Eloquent\Relations\BelongsTo`, `EventService`, `Event`, `.run`, `GameMatch`, `Competition.php`?**
  _High betweenness centrality (0.049) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `Illuminate\Http\RedirectResponse`, `.run`?**
  _High betweenness centrality (0.030) - this node is a cross-community bridge._
- **Why does `EventService` connect `EventService` to `Illuminate\Http\RedirectResponse`, `Competition`, `Event`, `Event.php`, `Schedule`?**
  _High betweenness centrality (0.029) - this node is a cross-community bridge._
- **Are the 4 inferred relationships involving `Competition` (e.g. with `.index()` and `.getCompetitionOverview()`) actually correct?**
  _`Competition` has 4 INFERRED edges - model-reasoned connections that need verification._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _179 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Illuminate\Http\RedirectResponse` be split into smaller, more focused modules?**
  _Cohesion score 0.05368289637952559 - nodes in this community are weakly interconnected._
- **Should `Competition` be split into smaller, more focused modules?**
  _Cohesion score 0.06220095693779904 - nodes in this community are weakly interconnected._