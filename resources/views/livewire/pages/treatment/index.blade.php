<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Make a Treatment Prediction!') }}
        </h2>
    </x-slot>
    <div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8">
        <livewire:pages.treatment.create />
        <livewire:pages.treatment.list />
    </div>
</x-app-layout>
