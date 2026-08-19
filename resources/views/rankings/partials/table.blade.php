<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Peringkat</th>
                <th>Peserta</th>
                @if ($showCompetition ?? false)
                    <th>Lomba</th>
                @endif
                <th>Main</th>
                <th>M</th>
                <th>S</th>
                <th>K</th>
                <th>Poin</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($leaderboard as $entry)
                <tr class="{{ ($entry->rank ?? $loop->iteration) <= 3 ? 'bg-primary/5' : '' }}">
                    <td>
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold {{ ($entry->rank ?? $loop->iteration) === 1 ? 'bg-amber-100 text-amber-700' : (($entry->rank ?? $loop->iteration) === 2 ? 'bg-slate-200 text-slate-600' : (($entry->rank ?? $loop->iteration) === 3 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-500')) }}">
                            {{ $entry->rank ?? $loop->iteration }}
                        </span>
                    </td>
                    <td>
                        @if (isset($entry->participant))
                            <a href="{{ route('participants.show', $entry->participant) }}" class="font-medium hover:text-primary">
                                {{ $entry->participant->name }}
                            </a>
                            <p class="text-xs text-slate-500">{{ $entry->participant->team ?? '' }}</p>
                        @else
                            -
                        @endif
                    </td>
                    @if ($showCompetition ?? false)
                        <td>{{ isset($entry->competitions_count) ? $entry->competitions_count . ' lomba' : ($entry->competition->name ?? '-') }}</td>
                    @endif
                    <td>{{ $entry->played ?? 0 }}</td>
                    <td class="text-emerald-600">{{ $entry->won ?? 0 }}</td>
                    <td class="text-amber-600">{{ $entry->drawn ?? 0 }}</td>
                    <td class="text-red-500">{{ $entry->lost ?? 0 }}</td>
                    <td class="font-bold text-primary">{{ $entry->points ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
