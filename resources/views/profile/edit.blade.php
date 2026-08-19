<x-app-layout title="Profil">
    <x-ui.page-header title="Profil" description="Kelola informasi akun Anda" />

    <div class="mx-auto max-w-3xl space-y-6">
        <x-ui.card>
            @include('profile.partials.update-profile-information-form')
        </x-ui.card>

        <x-ui.card>
            @include('profile.partials.update-password-form')
        </x-ui.card>

        <x-ui.card>
            @include('profile.partials.delete-user-form')
        </x-ui.card>
    </div>
</x-app-layout>
