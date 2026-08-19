<x-app-layout :title="'Bracket - ' . $competition->name">
    <x-ui.page-header :title="'Bracket'" :description="$competition->name">
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('competitions.show', $competition)">Kembali ke Lomba</x-ui.button>
            <x-ui.button variant="outline" :href="route('rankings.competition', $competition)">Peringkat</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card>
        @include('brackets.partials.visual', ['rounds' => $rounds, 'competition' => $competition, 'showGenerate' => true])
    </x-ui.card>
</x-app-layout>
