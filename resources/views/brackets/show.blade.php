<x-app-layout :title="'Bracket - ' . $competition->name">
    <x-ui.page-header :title="'Bracket Turnamen'" :description="$competition->name">
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('competitions.show', ['competition' => $competition, 'tab' => 'bracket'])">
                <i data-lucide="layout-grid" class="h-4 w-4"></i>
                Tab Lomba
            </x-ui.button>
            <x-ui.button variant="outline" :href="route('matches.index', ['competition_id' => $competition->id])">
                <i data-lucide="swords" class="h-4 w-4"></i>
                Semua Match
            </x-ui.button>
            <x-ui.button variant="outline" :href="route('rankings.competition', $competition)">
                <i data-lucide="medal" class="h-4 w-4"></i>
                Peringkat
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($competition->system === \App\Enums\CompetitionSystem::Point)
        @include('brackets.partials.point-system', ['rounds' => $rounds, 'competition' => $competition])
    @else
        @include('brackets.partials.visual', ['rounds' => $rounds, 'competition' => $competition, 'showGenerate' => true])
    @endif
</x-app-layout>
