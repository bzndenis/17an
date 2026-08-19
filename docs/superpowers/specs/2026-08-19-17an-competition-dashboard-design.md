# 17an Competition Dashboard — Design Spec

**Date:** 2026-08-19  
**Status:** Approved  
**Scope:** Full (Phase 1–4)

## Summary

Dashboard manajemen lomba 17 Agustus — modern tournament management platform untuk mengelola peserta, lomba, pertandingan, bracket knockout, sistem poin, ranking, dan statistik. Multi-event dengan event selector.

## Decisions

| Decision | Choice |
|----------|--------|
| Scope | Full Phase 1–4 |
| Auth | Single role Admin (Laravel Breeze) |
| Events | Multi-event dengan event selector di topbar |
| Architecture | Monolith Laravel Modular |
| Stack | Laravel 11, MySQL 8, Blade, Tailwind CSS, Alpine.js, Lucide Icons |
| Excel | maatwebsite/excel (Phase 4) |
| Charts | Chart.js (Phase 3–4) |

## Architecture

```
Laravel 11 + MySQL 8
├── Auth: Laravel Breeze (Blade)
├── UI: Tailwind CSS + Alpine.js + Lucide Icons
├── Excel: maatwebsite/excel
├── Struktur:
│   app/Services/     → BracketService, MatchService, RankingService, dll
│   app/Models/       → Eloquent + relationships
│   app/Policies/     → Admin-only authorization
│   resources/views/  → Blade components reusable
│   database/         → migrations + seeders
```

Multi-event: tabel `events` sebagai root. Semua data ter-scope ke `event_id`. Event selector menyimpan pilihan di session.

## Database Schema

### Core Tables

- **events** — name, slug, year, start_date, end_date, status, is_active
- **event_settings** — event_id, logo, theme_color, venue_default
- **participant_categories** — event_id, name
- **participants** — event_id, name, number, gender, dob, team, category_id, status, photo
- **competitions** — event_id, name, category, system, status, prizes, config (JSON)
- **competition_participants** — competition_id, participant_id, seed
- **rounds** — competition_id, name, round_number, type
- **matches** — round_id, competition_id, match_number, status, scheduled_at, venue, next_match_id
- **match_participants** — match_id, participant_id, score, is_winner
- **match_results** — match_id, winner_id, notes, finished_at
- **rankings** — competition_id, participant_id, played, won, drawn, lost, points, bonus
- **schedules** — event_id, title, datetime, location
- **announcements** — event_id, title, content, published_at
- **activity_logs** — user_id, event_id, action, description, metadata (JSON)
- **awards** — competition_id, participant_id, position, prize

### Enums

- Event: draft, active, completed, archived
- Competition: draft, registration, ready, ongoing, finished
- Match: upcoming, live, finished, cancelled
- Participant: active, eliminated, inactive
- System: knockout, point_system, round_robin, custom

### Constraints

- Index on event_id across scoped tables
- Unique: (event_id, participant_number), (competition_id, participant_id)
- Soft delete: participants, competitions, events

## Service Layer

| Service | Responsibility |
|---------|----------------|
| EventService | CRUD event, switch active event, settings |
| ParticipantService | CRUD, import/export Excel, bulk action |
| CompetitionService | Wizard create, status lifecycle |
| BracketService | Generate, seed, randomize, advance winner |
| MatchService | CRUD match, input result, reschedule |
| PointSystemService | Calculate points, update ranking, tie-breaker |
| RankingService | Leaderboard global & per competition |
| ActivityLogService | Log all important actions |
| DashboardService | Aggregate stats & recent activity |

All critical operations wrapped in DB transactions.

## Business Flows

### Create Competition (5-step wizard)

1. Info lomba → 2. Select participants → 3. Choose system → 4. Configure → 5. Review → Save Draft / Create

### Knockout Bracket

Generate bracket → assign participants → input results → auto-advance winner → finish → awards

### Point System

Generate schedule → input results → calculate points → update ranking → awards

### Multi-Event

Login → default active event → topbar selector → session scope → all queries filtered by event_id

## UI/UX

### Layout

Sidebar (collapsible) + Topbar (search, event selector, notification, profile, dark mode) + Content area

### Design Tokens

- Primary: #D71920
- Secondary: #111827
- Background: #F8FAFC
- Border radius: rounded-xl / rounded-2xl
- Font: Inter

### Key Pages

/dashboard, /participants, /competitions, /competitions/create, /competitions/{id}, /competitions/{id}/bracket, /competitions/{id}/ranking, /matches, /ranking, /settings

### UX Patterns

Toast, confirm dialog, skeleton loading, empty state, form validation, modal/drawer

## Implementation Phases

| Phase | Scope |
|-------|-------|
| 1 | Laravel setup, auth, layout, DB, dashboard, CRUD peserta & lomba, multi-event |
| 2 | Wizard, assign peserta, bracket knockout, match management, input hasil |
| 3 | Point system, ranking, statistik, jadwal, pengumuman, activity log |
| 4 | Import/export Excel, dark mode polish, charts, UX refinement |

## Seeder

2 events, 20 participants, 5 competitions (3 knockout + 2 point system), dummy matches & results.

## Security

Laravel auth, policies (admin-only), CSRF, validation, mass assignment protection, secure file upload, activity logging.
