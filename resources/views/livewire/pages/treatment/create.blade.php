<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div>
    <div class="max-w-2xl mx-auto mt-2 p-6 bg-white rounded-lg shadow-md">
        <form class="space-y-4" wire:submit="process">
            <div class="w-full">
                <label for="species" class="block text-sm font-medium text-gray-700">Species</label>
                <select id="species" name="species"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="">Select a species</option>
                    <option value="species1">Species 1</option>
                    <option value="species2">Species 2</option>
                    <option value="species3">Species 3</option>
                </select>
            </div>

            <div class="flex flex-wrap -mx-2">
                <div class="w-full md:w-1/2 px-2 mb-4">
                    <label for="concentration" class="block text-sm font-medium text-gray-700">Concentration
                        (1-100)</label>
                    <input type="number" id="concentration" name="concentration" min="1" max="100"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        required>
                </div>

                <div class="w-full md:w-1/2 px-2 mb-4">
                    <label for="soak-duration" class="block text-sm font-medium text-gray-700">Soak Duration (1-1440
                        minutes)</label>
                    <input type="number" id="soak-duration" name="soak-duration" min="1" max="1440"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        required>
                </div>

                <div class="w-full md:w-1/2 px-2 mb-4">
                    <label for="lowest-temp" class="block text-sm font-medium text-gray-700">Lowest Temperature
                        (°C)</label>
                    <input type="number" id="lowest-temp" name="lowest-temp"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        required>
                </div>

                <div class="w-full md:w-1/2 px-2 mb-4">
                    <label for="highest-temp" class="block text-sm font-medium text-gray-700">Highest Temperature
                        (°C)</label>
                    <input type="number" id="highest-temp" name="highest-temp"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        required>
                </div>

                <x-input-error :messages="$errors->get('message')" class="mt-2" />
            </div>

            <div class="flex justify-end">
                <x-primary-button class="mt-4">{{ __('Process') }}</x-primary-button>
            </div>
        </form>
    </div>
</div>
