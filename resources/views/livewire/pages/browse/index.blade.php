<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Browse Treatments!') }}
        </h2>
    </x-slot>
    <div class="max-w-6xl mx-auto p-4 sm:p-6 lg:p-8">
        <livewire:pages.browse.list />
    </div>
</x-app-layout>
