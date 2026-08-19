@php use App\Enums\EventStatus; @endphp

<x-app-layout title="Edit Event">
    <x-ui.page-header title="Edit Event" :description="$event->name">
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('events.index')">Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @include('events.form', ['event' => $event, 'statuses' => $statuses])
</x-app-layout>
