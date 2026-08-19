Bisa. Karena kamu ingin membuat **dashboard manajemen lomba 17 Agustus** dengan Laravel + MySQL, saya sarankan prompt-nya jangan hanya meminta “buat UI modern”, tetapi sekaligus mendefinisikan **struktur aplikasi, flow pertandingan, tipe lomba, CRUD, bracket, scoring, dan design system**.

Berikut prompt yang bisa langsung kamu pakai di **Cursor / Claude Code / AI coding assistant**:

Buatkan aplikasi **Dashboard Manajemen Lomba 17 Agustus** menggunakan:

* Backend: Laravel
* Database: MySQL
* Frontend: Blade + Tailwind CSS
* JavaScript: Alpine.js
* Icon: Lucide Icons
* Gunakan komponen UI yang reusable
* Responsive untuk desktop, tablet, dan mobile
* Gunakan desain modern, clean, premium, dan profesional
* Hindari tampilan admin template lama yang terlalu penuh tabel dan sidebar
* Gunakan card, statistik, badge, modal, dropdown, tabs, toast notification, empty state, skeleton loading, dan confirmation dialog secara konsisten.

## 1. Konsep Aplikasi

Aplikasi digunakan untuk mengelola perlombaan 17 Agustus.

Admin dapat:

1. Mengelola peserta
2. Mengelola jenis/kategori lomba
3. Membuat lomba
4. Menentukan sistem pertandingan
5. Menentukan peserta yang mengikuti lomba
6. Mengatur pertandingan
7. Melihat bracket pertandingan
8. Memasukkan hasil pertandingan
9. Mengelola sistem poin
10. Melihat ranking peserta
11. Melihat statistik lomba
12. Melihat dashboard keseluruhan kegiatan 17 Agustus

Aplikasi harus terasa seperti **modern tournament management dashboard**, bukan sekadar CRUD admin.

---

# 2. Struktur Navigasi

Buat sidebar utama dengan menu:

### Dashboard

* Overview

### Master Data

* Peserta
* Lomba
* Kategori Lomba

### Pertandingan

* Semua Pertandingan
* Bracket
* Klasemen / Ranking

### Event

* Jadwal
* Pengumuman

### Laporan

* Hasil Lomba
* Statistik
* Rekap Peserta

### Pengaturan

* Pengaturan Event
* User / Admin
* System Settings

Tambahkan topbar dengan:

* Search
* Notification
* Event selector
* Profile admin
* Toggle dark/light mode

Sidebar harus dapat collapse menjadi icon-only mode.

---

# 3. Dashboard

Buat dashboard utama dengan desain modern.

Bagian atas:

"17an Competition Dashboard"

Subheading:

"Kelola peserta, lomba, pertandingan, dan hasil kompetisi dalam satu tempat."

Tambahkan tombol:

* Tambah Lomba
* Tambah Peserta

## Statistik utama

Buat 4-6 statistic cards:

* Total Peserta
* Total Lomba
* Pertandingan Berlangsung
* Pertandingan Selesai
* Peserta Tereliminasi
* Total Hadiah

Gunakan visual yang menarik seperti:

* icon
* angka besar
* percentage
* trend indicator
* mini chart

## Competition Overview

Buat card yang menampilkan:

* Nama lomba
* Sistem pertandingan
* Jumlah peserta
* Progress pertandingan
* Status
* Action

Contoh:

Bakiak
Sistem: Knockout
16 Peserta
12 / 15 pertandingan selesai
80%

## Upcoming Matches

Tampilkan pertandingan terdekat:

Team A vs Team B
20 Agustus 2026 • 10:00
Lapangan 1

Tambahkan status:

Upcoming
Live
Finished

## Recent Activity

Tampilkan aktivitas:

* Peserta baru ditambahkan
* Hasil pertandingan diperbarui
* Lomba baru dibuat
* Peserta masuk semifinal
* Pertandingan selesai

---

# 4. CRUD PESERTA

Buat halaman `/peserta`.

Gunakan tabel modern.

Kolom:

* No
* Nama Peserta
* Nomor Peserta
* Jenis Kelamin
* Umur
* Kategori
* Tim
* Status
* Jumlah Lomba
* Action

Fitur:

* Search
* Filter kategori
* Filter gender
* Filter status
* Sort
* Pagination
* Bulk action
* Export Excel
* Import Excel

Action:

* Detail
* Edit
* Delete

## Tambah Peserta

Form:

* Nama lengkap
* Nomor peserta
* Foto
* Jenis kelamin
* Tanggal lahir
* Nomor HP
* Alamat
* RT/RW
* Tim
* Kategori
* Status

Gunakan modal atau drawer untuk form sederhana.

Untuk form kompleks gunakan halaman dedicated.

Tambahkan validasi realtime dan error message yang jelas.

---

# 5. CRUD LOMBA

Buat halaman `/lomba`.

Card atau table view dapat digunakan.

Setiap lomba menampilkan:

* Nama lomba
* Icon / thumbnail
* Kategori
* Jumlah peserta
* Sistem pertandingan
* Status
* Progress
* Jadwal
* Hadiah
* Action

Status:

* Draft
* Pendaftaran
* Siap Bertanding
* Berlangsung
* Selesai

Contoh lomba:

* Balap Karung
* Makan Kerupuk
* Estafet Sarung
* Bakiak
* Pecah Balon
* Futsal
* Voli
* Tarik Tambang

---

# 6. CREATE LOMBA

Buat wizard / multi-step form.

### Step 1 — Informasi Lomba

Input:

* Nama lomba
* Deskripsi
* Kategori
* Banner
* Lokasi
* Tanggal
* Jam
* Durasi
* Hadiah Juara 1
* Hadiah Juara 2
* Hadiah Juara 3

### Step 2 — Peserta

Pilih peserta yang mengikuti lomba.

Fitur:

* Search peserta
* Multi select
* Select all
* Remove participant

Tampilkan:

Selected Participants: 16

Gunakan participant cards dengan avatar dan nomor peserta.

### Step 3 — Sistem Pertandingan

Buat pilihan visual:

┌─────────────────────┐
│ 🏆 Knockout         │
│ Sistem gugur        │
│ Cocok untuk duel    │
└─────────────────────┘

┌─────────────────────┐
│ ⭐ Point System      │
│ Sistem poin         │
│ Ranking berdasarkan  │
│ total poin           │
└─────────────────────┘

Pilihan:

1. Knockout / Sistem Gugur
2. Point System
3. Round Robin
4. Custom

Untuk MVP prioritaskan:

* Knockout
* Point System

### Step 4 — Konfigurasi

Jika Knockout:

* Jumlah ronde
* Best of
* Seeding
* Randomize peserta
* Third place match

Jika Point System:

* Jumlah ronde
* Poin menang
* Poin seri
* Poin kalah
* Poin bonus
* Tie breaker

### Step 5 — Review

Tampilkan ringkasan:

Nama lomba
Peserta
Sistem
Jadwal
Lokasi
Hadiah

Tombol:

Simpan sebagai Draft
Buat Lomba

---

# 7. SISTEM KNOCKOUT / SISTEM GUGUR

Ini merupakan fitur penting.

Buat halaman:

`/lomba/{id}/bracket`

Tampilkan tournament bracket modern.

Contoh:

ROUND 1        SEMIFINAL        FINAL

Peserta A ─┐
├── Winner ─┐
Peserta B ─┘           │
├── Winner ── 🏆 Champion
Peserta C ─┐           │
├── Winner ─┘
Peserta D ─┘

Bracket harus visual dan mudah dibaca.

Setiap match card menampilkan:

Match #01

Peserta A     2
Peserta B     1

Status:
Finished

Tambahkan tombol:

Input Result
Edit
View Detail

Untuk pertandingan yang belum berlangsung:

Upcoming

Untuk pertandingan sedang berlangsung:

LIVE

Gunakan visual connector antar ronde.

---

# 8. INTERAKSI BRACKET

Bracket harus memiliki interaction yang baik.

Admin dapat:

* Generate bracket
* Randomize peserta
* Seed peserta
* Drag/drop peserta jika memungkinkan
* Input score
* Menentukan winner
* Auto advance winner ke ronde berikutnya
* Reset match
* Edit match
* Reschedule
* Set status pertandingan

Saat hasil pertandingan disimpan:

Winner otomatis masuk ke match berikutnya.

Contoh:

Match 1:
A 5 - 3 B

Winner A

Maka A otomatis masuk ke semifinal.

Gunakan transaction database agar update bracket aman.

---

# 9. SISTEM POINT

Untuk lomba dengan sistem poin, buat halaman:

`/lomba/{id}/ranking`

Tampilkan leaderboard modern.

Kolom:

Rank
Peserta
Main
Menang
Seri
Kalah
Poin
Bonus
Total

Contoh:

🥇 1  Andi      5  4  1  0  12
🥈 2  Budi      5  3  1  1  10
🥉 3  Candra    5  3  0  2   9

Highlight:

Top 3 peserta.

Gunakan podium visual untuk:

🥇 Juara 1
🥈 Juara 2
🥉 Juara 3

Tambahkan:

* ranking
* total points
* win rate
* match played
* streak

---

# 10. INPUT HASIL PERTANDINGAN

Buat modal / drawer modern.

Contoh:

MATCH #12

Andi
VS
Budi

Score:

Andi [ 5 ]
Budi [ 3 ]

Winner:

● Andi
○ Budi

Catatan:

[________________]

Status:

Finished

Button:

Cancel
Save Result

Jika sistem point:

tambahkan input:

Winner Points
Loser Points
Bonus Points

Setelah disimpan:

* update match
* update ranking
* update bracket jika knockout
* simpan activity log

---

# 11. DETAIL LOMBA

Buat halaman detail dengan layout dashboard.

Header:

Nama Lomba
Status
Kategori

Action:

Edit
Manage Participants
Generate Bracket
Input Result

Tabs:

Overview
Participants
Matches
Bracket
Ranking
Schedule
History

Overview berisi:

* statistik
* progress
* upcoming match
* recent results
* leaderboard

---

# 12. HALAMAN PERTANDINGAN

Buat halaman `/matches`.

Filter:

* Semua
* Upcoming
* Live
* Finished
* Cancelled

Card pertandingan:

MATCH #24

Andi
VS
Budi

10:30
Lapangan 1

Status LIVE

Button:

Manage Match

---

# 13. HALAMAN RANKING GLOBAL

Buat halaman ranking seluruh event.

Leaderboard:

Rank
Peserta
Lomba
Match
Win
Loss
Points

Tambahkan filter:

* Lomba
* Kategori
* Gender
* Tim

Tambahkan chart:

* Top participants
* Points distribution
* Win rate

---

# 14. DATA MODEL

Buat database schema yang scalable.

Minimal tabel:

users

participants

participant_categories

competitions

competition_participants

competition_rounds

matches

match_participants

match_results

points

rankings

schedules

awards

activity_logs

event_settings

Gunakan:

* foreign key
* index
* unique constraint
* soft delete jika diperlukan
* timestamps

Gunakan Laravel migration dan Eloquent relationship yang proper.

---

# 15. RELATIONSHIP

Contoh:

Participant
hasMany CompetitionParticipant

Competition
belongsToMany Participant

Competition
hasMany Rounds

Round
hasMany Matches

Match
hasMany MatchParticipants

Match
hasOne MatchResult

Competition
hasMany Rankings

Pastikan relationship Eloquent dibuat dengan jelas.

---

# 16. BUSINESS LOGIC

Pisahkan business logic dari controller.

Gunakan Service class.

Contoh:

CompetitionService

BracketService

MatchService

RankingService

PointSystemService

ParticipantService

Contoh:

BracketService:

* generateBracket()
* seedParticipants()
* createRounds()
* createMatches()
* advanceWinner()
* resetBracket()

PointSystemService:

* calculatePoints()
* updateRanking()
* recalculateRanking()

MatchService:

* createMatch()
* updateResult()
* finishMatch()
* rescheduleMatch()

Controller harus tipis dan business logic berada di service layer.

---

# 17. UI/UX DESIGN SYSTEM

Gunakan design system modern.

Style:

* Minimal
* Premium
* Clean
* Sport/event management dashboard
* Tidak terlalu banyak warna
* Banyak whitespace
* Rounded corners
* Soft shadow
* Subtle border
* Smooth hover transition
* Micro interaction

Gunakan:

* rounded-xl / rounded-2xl
* shadow-sm
* border
* backdrop blur jika sesuai
* gradient hanya sebagai accent
* large typography untuk statistic

Gunakan semantic color:

Success → green
Warning → amber
Danger → red
Info → blue
Live → red indicator

Buat dark mode.

---

# 18. WARNA

Gunakan tema utama yang terinspirasi dari semangat kemerdekaan Indonesia:

Primary:
#D71920

Secondary:
#111827

Background:
#F8FAFC

White:
#FFFFFF

Tetapi jangan membuat seluruh halaman merah.

Merah hanya digunakan sebagai:

* primary action
* active state
* accent
* badge
* highlight

UI tetap terlihat profesional dan modern.

---

# 19. RESPONSIVE

Desktop:

Sidebar + Topbar + Content

Tablet:

Collapsible Sidebar

Mobile:

Bottom navigation atau mobile drawer.

Bracket pada mobile:

Gunakan horizontal scrolling yang smooth.

Jangan memaksakan seluruh bracket masuk ke layar mobile.

---

# 20. UX DETAIL

Tambahkan:

* Toast notification
* Confirmation dialog
* Loading state
* Skeleton state
* Empty state
* Error state
* Form validation
* Disabled state
* Hover state
* Focus state
* Keyboard accessibility

Contoh empty state:

"Belum ada peserta"

"Tambahkan peserta untuk memulai lomba."

Button:

* Tambah Peserta

---

# 21. DASHBOARD VISUAL

Jangan membuat dashboard seperti CRUD biasa.

Gunakan hierarchy:

HEADER
↓
STATISTICS
↓
ACTIVE COMPETITIONS
↓
UPCOMING MATCHES + LIVE MATCH
↓
LEADERBOARD + ACTIVITY
↓
QUICK ACTIONS

Gunakan grid responsive.

---

# 22. COMPONENT STRUCTURE

Buat reusable Blade components:

components/
ui/
button.blade.php
card.blade.php
badge.blade.php
modal.blade.php
dropdown.blade.php
input.blade.php
select.blade.php
table.blade.php
pagination.blade.php
toast.blade.php
empty-state.blade.php
stat-card.blade.php

components/
competition/
competition-card.blade.php
competition-status.blade.php
participant-selector.blade.php

components/
match/
match-card.blade.php
score-card.blade.php
result-modal.blade.php

components/
bracket/
bracket.blade.php
round.blade.php
match.blade.php
connector.blade.php

---

# 23. ROUTING

Gunakan route yang RESTful.

Contoh:

/dashboard

/participants

/participants/create

/participants/{participant}

/competitions

/competitions/create

/competitions/{competition}

/competitions/{competition}/participants

/competitions/{competition}/bracket

/competitions/{competition}/ranking

/competitions/{competition}/matches

/matches

/matches/{match}

/matches/{match}/result

/settings

Gunakan resource controller jika sesuai.

---

# 24. SECURITY

Implementasikan:

* Laravel authentication
* Authorization / Policy
* CSRF
* Validation
* Mass assignment protection
* Rate limiting untuk endpoint tertentu
* Secure file upload
* Activity logging

Admin hanya boleh mengubah data sesuai permission.

---

# 25. SEEDER

Buat database seeder dengan data dummy realistis.

Minimal:

20 peserta

5 lomba

Contoh:

Bakiak
Tarik Tambang
Balap Karung
Makan Kerupuk
Estafet Sarung

Buat beberapa lomba dengan:

* Knockout
* Point System

Generate pertandingan dummy agar dashboard langsung terlihat hidup.

---

# 26. PRIORITAS IMPLEMENTASI

Kerjakan secara bertahap.

PHASE 1:

* Laravel setup
* Authentication
* Layout
* Sidebar
* Dashboard
* Database
* Participant CRUD
* Competition CRUD

PHASE 2:

* Competition participant
* Match management
* Knockout bracket
* Result management

PHASE 3:

* Point system
* Ranking
* Statistics
* Schedule

PHASE 4:

* Export/import
* Activity log
* Advanced filters
* Dark mode
* UX polish

---

# 27. ATURAN CODING

Gunakan:

* Laravel best practices
* Eloquent ORM
* Form Request validation
* Policies
* Service layer
* Blade components
* Tailwind utility classes
* Alpine.js untuk interaction ringan

Hindari:

* business logic kompleks di Blade
* business logic kompleks di Controller
* query database berulang
* hardcoded data
* duplicate component
* inline CSS jika bisa menggunakan Tailwind
* JavaScript framework besar jika tidak diperlukan

Gunakan database transaction untuk proses penting seperti:

* generate bracket
* update result
* advance winner
* recalculate ranking

---

# 28. HASIL AKHIR YANG DIHARAPKAN

Aplikasi harus terlihat seperti:

"Modern Tournament Management Platform"

bukan:

"Admin CRUD Website".

Prioritaskan:

1. Visual hierarchy
2. User experience
3. Tournament workflow
4. Bracket visualization
5. Easy result management
6. Responsive design
7. Reusable components
8. Clean Laravel architecture

Sebelum membuat kode, analisis terlebih dahulu struktur database dan flow pertandingan.

Setelah itu implementasikan secara bertahap.

Untuk setiap fitur yang dibuat:

1. Buat migration
2. Buat model + relationship
3. Buat Form Request
4. Buat Service
5. Buat Controller
6. Buat route
7. Buat Blade UI
8. Buat validation
9. Buat seeder jika diperlukan
10. Test flow end-to-end

Pastikan seluruh fitur saling terintegrasi.

Fokus utama aplikasi adalah **pengelolaan lomba 17 Agustus dengan dua sistem pertandingan utama: Knockout/Sistem Gugur dan Point System**, dengan UI/UX modern berbasis Tailwind CSS.
