<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Http;

new class extends Component {
    public $showModal = false;
    public $isSuccess = false;
    public $species = '';
    public $concentration;
    public $soakDuration;
    public $lowestTemp;
    public $highestTemp;

    // New properties for response data
    public $result;
    public $confidenceScore;
    public $successRate;

    public function rules()
    {
        return [
            'species' => 'required',
            'concentration' => 'required|numeric|min:1|max:100',
            'soakDuration' => 'required|numeric|min:1|max:1440',
            'lowestTemp' => 'required|numeric',
            'highestTemp' => 'required|numeric',
        ];
    }

    public function processForm()
    {
        $this->validate();

        try {
            $response = Http::post('http://127.0.0.1:8000/process', [
                'species' => 'Rice',
                'emsConcentration' => (float) $this->concentration,
                'soakDuration' => (int) $this->soakDuration,
                'lowestTemp' => (float) $this->lowestTemp,
                'highestTemp' => (float) $this->highestTemp,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->result = $data['result'];
                $this->confidenceScore = $data['confidence_score'];
                $this->successRate = $data['success_rate'];
                $this->isSuccess = true;
            } else {
                $this->isSuccess = false;
            }

            $this->showModal = true;

        } catch (\Exception $e) {
            $this->isSuccess = false;
            $this->showModal = true;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function save()
    {
        $this->closeModal();
    }
}; ?>

<div class="relative">
    <div class="max-w-2xl mx-auto mt-2 p-6 bg-white rounded-lg shadow-md">
        <form class="space-y-4" wire:submit="processForm">
            <!-- Form fields remain the same -->
            <div class="w-full">
                <label for="species" class="block text-sm font-medium text-gray-700">Species</label>
                <select id="species" wire:model="species"
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
                    <input type="number" id="concentration" wire:model="concentration" min="1" max="100"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        required>
                </div>

                <div class="w-full md:w-1/2 px-2 mb-4">
                    <label for="soakDuration" class="block text-sm font-medium text-gray-700">Soak Duration (1-1440
                        minutes)</label>
                    <input type="number" id="soakDuration" wire:model="soakDuration" min="1" max="1440"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        required>
                </div>

                <div class="w-full md:w-1/2 px-2 mb-4">
                    <label for="lowestTemp" class="block text-sm font-medium text-gray-700">Lowest Temperature
                        (°C)</label>
                    <input type="number" id="lowestTemp" wire:model="lowestTemp"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        required>
                </div>

                <div class="w-full md:w-1/2 px-2 mb-4">
                    <label for="highestTemp" class="block text-sm font-medium text-gray-700">Highest Temperature
                        (°C)</label>
                    <input type="number" id="highestTemp" wire:model="highestTemp"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        required>
                </div>
            </div>

            <div class="flex justify-end">
                <x-primary-button class="mt-4">{{ __('Process') }}</x-primary-button>
            </div>
        </form>
    </div>

    @if($showModal)
    <div class="fixed inset-0 z-10 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:w-3/5 sm:max-w-3xl {{ $isSuccess ? 'bg-green-100' : 'bg-red-100' }}">
                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    @if($isSuccess)
                        <div class="space-y-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10 bg-green-100">
                                    <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-green-900">Processing Results</h3>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-4 mt-4">
                                <div class="bg-white p-4 rounded-lg shadow">
                                    <div class="text-sm font-medium text-gray-500">Result</div>
                                    <div class="mt-1 text-2xl font-semibold">{{ $result }}</div>
                                </div>
                                <div class="bg-white p-4 rounded-lg shadow">
                                    <div class="text-sm font-medium text-gray-500">Confidence Score</div>
                                    <div class="mt-1 text-2xl font-semibold">{{ $confidenceScore }}%</div>
                                </div>
                                <div class="bg-white p-4 rounded-lg shadow">
                                    <div class="text-sm font-medium text-gray-500">Success Rate</div>
                                    <div class="mt-1 text-2xl font-semibold">{{ $successRate }}%</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10 bg-red-100">
                                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-red-900">Processing Failed</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-red-700">There was an error processing your form. Please try again.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    @if($isSuccess)
                    <button type="button"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm"
                        wire:click="save">
                        Save
                    </button>
                    @endif
                    <button type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        wire:click="closeModal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
