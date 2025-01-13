<?php


use App\Models\Species;
use App\Models\Treatment;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Http;

new class extends Component {
    public $showModal = false;
    public $isLoading = false;
    public $isSuccess = false;
    public $selectedSpecies;
    public $concentration;
    public $soakDuration;
    public $lowestTemp;
    public $highestTemp;

    public Collection $species;

    public $result;
    public $successRate;

    public function mount()
    {
        $this->species = Species::all();
    }

    public function rules()
    {
        return [
            'selectedSpecies' => 'required',
            'concentration' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/|min:0.01|max:100',
            'soakDuration' => 'required|numeric|min:1|max:1440',
            'lowestTemp' => 'required|numeric',
            'highestTemp' => 'required|numeric',
        ];
    }

    public function processForm()
    {
        $this->validate();

        $this->isLoading = true;
        $this->showModal = true;

        try {
            $response = Http::post('http://127.0.0.1:8000/process', [
                'species' => $this->selectedSpecies,
                'emsConcentration' => (float) $this->concentration,
                'soakDuration' => (int) $this->soakDuration,
                'lowestTemp' => (float) $this->lowestTemp,
                'highestTemp' => (float) $this->highestTemp,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->result = $data['result'];
                $this->successRate = $data['success_rate'];
                $this->isSuccess = $this->result == 1;
            } else {
                $this->isSuccess = false;
            }

        } catch (\Exception $e) {
            $this->isSuccess = false;
        } finally {
            $this->isLoading = false;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->isLoading = false;
    }

    public function save()
    {
        $speciesId = Species::where('name', $this->selectedSpecies)->first()->id;

        Treatment::create([
            'species_id' => $speciesId,
            'emsConcentration' => (float) $this->concentration,
            'soakDuration' => (int) $this->soakDuration,
            'lowestTemp' => (float) $this->lowestTemp,
            'highestTemp' => (float) $this->highestTemp,
            'result' => $this->result,
            'successRate' => $this->successRate,
        ]);

        $this->closeModal();
    }
}; ?>

<div class="relative">
    <div class="max-w-2xl mx-auto mt-2 p-6 bg-white rounded-lg shadow-md">
        <form class="space-y-4" wire:submit="processForm">
            <div class="w-full">
                <label for="selectedSpecies" class="block text-sm font-medium text-gray-700">Species</label>
                <select id="selectedSpecies" wire:model="selectedSpecies"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="">Select a species</option>
                    @foreach ($species as $item)
                    <option value="{{ $item->name }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-wrap -mx-2">
                <div class="w-full md:w-1/2 px-2 mb-4">
                    <label for="concentration" class="block text-sm font-medium text-gray-700">Concentration
                        (1-100)</label>
                    <input type="text" id="concentration" wire:model="concentration" min="0.01" max="100"
                        pattern="^\d+(\.\d{1,2})?$"
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
                <x-primary-button class="mt-4" wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ __('Process') }}</span>
                    <span wire:loading>Processing...</span>
                </x-primary-button>
            </div>
        </form>
    </div>

    @if($showModal)
    <div class="fixed inset-0 z-10 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                class="inline-block align-bottom rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:w-3/5 sm:max-w-3xl {{ !$isLoading && $isSuccess ? 'bg-green-100' : (!$isLoading ? 'bg-red-100' : 'bg-white') }}">
                @if($isLoading)
                <div class="p-6 flex flex-col items-center justify-center">
                    <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-indigo-500 mb-4"></div>
                    <h3 class="text-lg font-medium text-gray-900">Processing Results</h3>
                    <p class="mt-2 text-sm text-gray-500">Please wait while we analyze your parameters...</p>
                </div>
                @else
                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="space-y-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10 {{ $isSuccess ? 'bg-green-100' : 'bg-red-100' }}">
                                @if($isSuccess)
                                <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                @else
                                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                @endif
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3
                                    class="text-lg leading-6 font-medium {{ $isSuccess ? 'text-green-900' : 'text-red-900' }}">
                                    Processing Results
                                </h3>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div class="bg-white p-4 rounded-lg shadow">
                                <div class="text-sm font-medium text-gray-500">Result</div>
                                <div
                                    class="mt-1 text-2xl font-semibold {{ $isSuccess ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $isSuccess ? 'Success' : 'Failed' }}
                                </div>
                            </div>
                            <div class="bg-white p-4 rounded-lg shadow">
                                <div class="text-sm font-medium text-gray-500">Success Rate</div>
                                <div class="mt-1 text-2xl font-semibold">{{ $successRate }}%</div>
                            </div>
                        </div>

                        <div class="mt-2">
                            <p class="text-sm {{ $isSuccess ? 'text-green-700' : 'text-red-700' }}">
                                {{ $isSuccess
                                ? 'The process was successful with the given parameters.'
                                : 'The process was unsuccessful with the given parameters. Consider adjusting your
                                inputs.' }}
                            </p>
                        </div>
                    </div>
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
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
