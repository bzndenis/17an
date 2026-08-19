<x-app-layout title="Peringkat Global">
    <x-ui.page-header title="Peringkat Global" description="Agregasi poin semua lomba event aktif" />

    <x-ui.card class="mb-6">
        <form method="GET" class="grid gap-4 sm:grid-cols-4">
            <div>
                <label class="form-label">Lomba</label>
                <select name="competition_id" class="form-select">
                    <option value="">Semua</option>
                    @foreach ($competitions as $comp)
                        <option value="{{ $comp->id }}" @selected(($filters['competition_id'] ?? '') == $comp->id)>{{ $comp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Kategori</label>
                <select name="category_id" class="form-select">
                    <option value="">Semua</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(($filters['category_id'] ?? '') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select">
                    <option value="">Semua</option>
                    <option value="male" @selected(($filters['gender'] ?? '') === 'male')>Laki-laki</option>
                    <option value="female" @selected(($filters['gender'] ?? '') === 'female')>Perempuan</option>
                </select>
            </div>
            <div>
                <label class="form-label">Tim</label>
                <input type="text" name="team" value="{{ $filters['team'] ?? '' }}" class="form-input" placeholder="Nama tim">
            </div>
            <div class="sm:col-span-4 flex gap-2">
                <x-ui.button type="submit">Filter</x-ui.button>
                <x-ui.button variant="outline" :href="route('rankings.global')">Reset</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    @if ($rankings->isEmpty())
        <x-ui.empty-state title="Belum Ada Data Peringkat" description="Peringkat global akan muncul setelah ada pertandingan selesai." icon="medal" />
    @else
        <div class="mb-6 grid gap-6 lg:grid-cols-2">
            <x-ui.card title="Top 10 Peserta (Poin)">
                <canvas id="pointsChart" height="200"
                    data-labels='@json($chartData['labels'])'
                    data-points='@json($chartData['points'])'></canvas>
            </x-ui.card>
            <x-ui.card title="Top 10 Peserta (Kemenangan)">
                <canvas id="winsChart" height="200"
                    data-labels='@json($chartData['labels'])'
                    data-wins='@json($chartData['wins'])'></canvas>
            </x-ui.card>
        </div>

        @include('rankings.partials.podium', ['leaderboard' => $rankings])
        <x-ui.card :padding="false">
            @include('rankings.partials.table', ['leaderboard' => $rankings, 'showCompetition' => true])
        </x-ui.card>
    @endif

    @push('scripts')
    <script type="module">
        import { Chart, BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend } from 'chart.js';
        Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend);

        const chartDefaults = { responsive: true, maintainAspectRatio: false };

        const pointsEl = document.getElementById('pointsChart');
        if (pointsEl) {
            new Chart(pointsEl, {
                type: 'bar',
                data: {
                    labels: JSON.parse(pointsEl.dataset.labels),
                    datasets: [{ label: 'Total Poin', data: JSON.parse(pointsEl.dataset.points), backgroundColor: '#D71920', borderRadius: 8 }],
                },
                options: { ...chartDefaults, plugins: { legend: { display: false } } },
            });
        }

        const winsEl = document.getElementById('winsChart');
        if (winsEl) {
            new Chart(winsEl, {
                type: 'bar',
                data: {
                    labels: JSON.parse(winsEl.dataset.labels),
                    datasets: [{ label: 'Kemenangan', data: JSON.parse(winsEl.dataset.wins), backgroundColor: '#111827', borderRadius: 8 }],
                },
                options: { ...chartDefaults, plugins: { legend: { display: false } } },
            });
        }
    </script>
    @endpush
</x-app-layout>
