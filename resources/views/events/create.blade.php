@php use App\Enums\EventStatus; @endphp

<x-app-layout title="Tambah Event">
    <x-ui.page-header title="Tambah Event" description="Buat event/perayaan baru">
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('events.index')">Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @include('events.form', ['event' => null, 'statuses' => $statuses])
</x-app-layout>
