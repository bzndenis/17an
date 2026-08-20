@php
    use App\Enums\CompetitionSystem;
@endphp

<x-app-layout :title="'Bracket - ' . $competition->name">
    <x-ui.page-header :title="'Bracket Turnamen'" :description="$competition->name">
        <x-slot:actions>
            @if ($canRandomizeMatches ?? false)
                <form
                    action="{{ route('brackets.randomize', $competition) }}"
                    method="POST"
                    onsubmit="return confirm('Acak ulang peserta? Seed dan pasangan akan diganti.')"
                >
                    @csrf
                    <x-ui.button variant="outline" type="submit">
                        <i data-lucide="shuffle" class="h-4 w-4"></i>
                        Acak Peserta
                    </x-ui.button>
                </form>
            @endif
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

    @if ($competition->system === CompetitionSystem::Point || $competition->system === CompetitionSystem::League)
        @include('brackets.partials.point-system', [
            'rounds' => $rounds,
            'competition' => $competition,
            'canRandomizeMatches' => $canRandomizeMatches ?? false,
        ])
    @elseif ($competition->system === CompetitionSystem::GroupKnockout)
        @include('brackets.partials.group-knockout', [
            'rounds' => $rounds,
            'competition' => $competition,
            'groupEntries' => $groupEntries ?? collect(),
            'canRandomizeMatches' => $canRandomizeMatches ?? false,
        ])
    @else
        @include('brackets.partials.visual', [
            'rounds' => $rounds,
            'competition' => $competition,
            'showGenerate' => true,
            'canRandomizeMatches' => $canRandomizeMatches ?? false,
        ])
    @endif
</x-app-layout>
