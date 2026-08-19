# 17an Competition Dashboard

Dashboard manajemen lomba 17 Agustus — modern tournament management platform.

## Stack

- Laravel 13 + MySQL 8
- Blade + Tailwind CSS + Alpine.js
- Lucide Icons
- maatwebsite/excel (import/export)
- Chart.js (statistik)

## Setup (Laragon)

### 1. Database

Buat database MySQL `17an` (sudah otomatis jika Laragon MySQL aktif).

### 2. Environment

File `.env` sudah dikonfigurasi:

```
APP_URL=http://17an.test
DB_DATABASE=17an
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Install & Migrate

```bash
cd c:\laragon\www\17an
composer install
npm install
php artisan migrate:fresh --seed
php artisan storage:link
npm run build
```

### 4. Virtual Host (Laragon)

Folder sudah di `c:\laragon\www\17an`. Akses via:

- **http://17an.test** (jika Laragon auto virtual host aktif)
- atau `php artisan serve` → http://localhost:8000

## Login

| Field | Value |
|-------|-------|
| Email | admin@17an.test |
| Password | password |

## Fitur

- Multi-event dengan event selector
- CRUD Peserta (import/export Excel)
- CRUD Lomba + wizard multi-step
- Bracket knockout visual + auto-advance winner
- Sistem poin + ranking per lomba & global
- Manajemen pertandingan + input hasil
- Jadwal & pengumuman
- Dashboard statistik & activity log
- Dark mode

## Dummy Data (Seeder)

- 2 events (2025 selesai, 2026 aktif)
- 20 peserta, 5 lomba (3 knockout + 2 point system)
- Pertandingan & hasil dummy

## Struktur

```
app/
├── Enums/          # Status enums
├── Exports/        # Excel export
├── Imports/        # Excel import
├── Models/         # Eloquent models
├── Services/       # Business logic
└── Http/Controllers/

resources/views/
├── components/ui/  # Reusable UI components
├── layouts/        # Admin layout (sidebar + topbar)
├── participants/
├── competitions/
├── brackets/
├── matches/
└── rankings/
```

## Development

```bash
npm run dev          # Vite dev server
php artisan serve    # Laravel dev server
```

## Dokumentasi Desain

Lihat `docs/superpowers/specs/2026-08-19-17an-competition-dashboard-design.md`
