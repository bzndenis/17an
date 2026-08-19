<x-app-layout :title="'Peringkat - ' . $competition->name">
    <x-ui.page-header :title="'Peringkat Lomba'" :description="$competition->name">
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('competitions.show', $competition)">Kembali</x-ui.button>
            <x-ui.button variant="outline" :href="route('rankings.global')">Peringkat Global</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($leaderboard->isEmpty())
        <x-ui.empty-state title="Belum Ada Peringkat" description="Peringkat akan muncul setelah pertandingan selesai." icon="medal" />
    @else
        @include('rankings.partials.podium', ['leaderboard' => $leaderboard])
        <x-ui.card :padding="false">
            @include('rankings.partials.table', ['leaderboard' => $leaderboard, 'showCompetition' => false])
        </x-ui.card>
    @endif
</x-app-layout>
