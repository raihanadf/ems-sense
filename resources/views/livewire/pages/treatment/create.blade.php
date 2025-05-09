<?php
use App\Models\Species;
use App\Models\Treatment;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public $showModal = false;
    public $isLoading = false;
    public $isSuccess = false;
    public $selectedSpecies;
    public $concentration;
    public $soakDuration;
    public $lowestTemp;
    public $highestTemp;
    public $manualOverride = false;
    public $overrideResult = false;
    public $note = '';

    public Collection $species;

    public $minConcentration;
    public $maxConcentration;
    public $minSoakDuration;
    public $maxSoakDuration;
    public $minLowestTemp;
    public $maxLowestTemp;
    public $minHighestTemp;
    public $maxHighestTemp;

    public $result;
    public $successRate;

    public function mount()
    {
        $this->species = Species::all();

        $this->fetchDynamicLimits();
    }

    public function fetchDynamicLimits()
    {
        $stats = DB::table('treatments')
            ->selectRaw('
                MIN(emsConcentration) as min_concentration,
                MAX(emsConcentration) as max_concentration,
                MIN(soakDuration) as min_soak_duration,
                MAX(soakDuration) as max_soak_duration,
                MIN(lowestTemp) as min_lowest_temp,
                MAX(lowestTemp) as max_lowest_temp,
                MIN(highestTemp) as min_highest_temp,
                MAX(highestTemp) as max_highest_temp
            ')
            ->first();

        $this->minConcentration = $stats->min_concentration ?? 0.01;
        $this->maxConcentration = $stats->max_concentration ?? 100;
        $this->minSoakDuration = $stats->min_soak_duration ?? 1;
        $this->maxSoakDuration = $stats->max_soak_duration ?? 1440;
        $this->minLowestTemp = $stats->min_lowest_temp ?? 0;
        $this->maxLowestTemp = $stats->max_lowest_temp ?? 100;
        $this->minHighestTemp = $stats->min_highest_temp ?? 0;
        $this->maxHighestTemp = $stats->max_highest_temp ?? 100;
    }

    public function rules()
    {
        return [
            'selectedSpecies' => 'required',
            'concentration' => 'required|numeric',
            'soakDuration' => 'required|numeric',
            'lowestTemp' => 'required|numeric',
            'highestTemp' => 'required|numeric',
            'note' => 'nullable|string|max:500',
        ];
    }

    public function processForm()
    {
        $this->validate();

        $this->isLoading = true;

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
                $this->overrideResult = (bool)$this->result; // Convert to boolean
            } else {
                $this->isSuccess = false;
                $this->overrideResult = false;
            }

        } catch (\Exception $e) {
            $this->isSuccess = false;
            $this->overrideResult = false;
        } finally {
            $this->isLoading = false;
            $this->showModal = true;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->isLoading = false;
        $this->note = '';
        $this->manualOverride = false;
    }

    public function toggleOverride()
    {
        $this->manualOverride = !$this->manualOverride;
    }

    public function updateOverrideResult($value)
    {
        $this->overrideResult = (bool)$value;
    }

    public function save()
    {
        $this->validate([
            'note' => 'nullable|string|max:500',
        ]);

        $speciesId = Species::where('name', $this->selectedSpecies)->first()->id;
        $userId = auth()->user()->id;
        $finalResult = $this->manualOverride ? ($this->overrideResult ? 1 : 0) : $this->result;

        Treatment::create([
            'user_id' => $userId,
            'species_id' => $speciesId,
            'emsConcentration' => (float) $this->concentration,
            'soakDuration' => (int) $this->soakDuration,
            'lowestTemp' => (float) $this->lowestTemp,
            'highestTemp' => (float) $this->highestTemp,
            'result' => $finalResult,
            'successRate' => $this->successRate,
            'note' => $this->note,
            'overridden' => $this->manualOverride,
        ]);

        Notification::make()
            ->title('Treatment Saved!')
            ->success()
            ->send();

        $this->dispatch('treatment-created');
        $this->closeModal();

        // After saving, refresh the dynamic limits to include the new data
        $this->fetchDynamicLimits();
    }
}; ?>

<div class="relative">
    <div class="max-w-4xl mx-auto mt-2 p-6 bg-white rounded-lg shadow-md">
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
                    <label for="concentration" class="block text-sm font-medium text-gray-700">
                        Concentration (observed range: {{ number_format($minConcentration, 2) }} - {{
                        number_format($maxConcentration, 2) }})
                    </label>
                    <input type="text" id="concentration" wire:model="concentration"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        required>
                </div>

                <div class="w-full md:w-1/2 px-2 mb-4">
                    <label for="soakDuration" class="block text-sm font-medium text-gray-700">
                        Soak Duration (observed range: {{ $minSoakDuration }} - {{ $maxSoakDuration }} minutes)
                    </label>
                    <input type="number" id="soakDuration" wire:model="soakDuration"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        required>
                </div>

                <div class="w-full md:w-1/2 px-2 mb-4">
                    <label for="lowestTemp" class="block text-sm font-medium text-gray-700">
                        Lowest Temperature (observed range: {{ number_format($minLowestTemp, 1) }} - {{
                        number_format($maxLowestTemp, 1) }} °C)
                    </label>
                    <input type="number" id="lowestTemp" wire:model="lowestTemp"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        required>
                </div>

                <div class="w-full md:w-1/2 px-2 mb-4">
                    <label for="highestTemp" class="block text-sm font-medium text-gray-700">
                        Highest Temperature (observed range: {{ number_format($minHighestTemp, 1) }} - {{
                        number_format($maxHighestTemp, 1) }} °C)
                    </label>
                    <input type="number" id="highestTemp" wire:model="highestTemp"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        required>
                </div>
            </div>

            <div class="flex justify-end">
                <x-primary-button class="flex items-center gap-2" wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ __('Process') }}</span>
                    <span wire:loading>Processing</span>
                    <span wire:loading class="inline-block">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </span>
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
                class="inline-block align-bottom rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:w-3/5 sm:max-w-3xl {{ !$isLoading && ($manualOverride ? $overrideResult : $isSuccess) ? 'bg-green-100' : (!$isLoading ? 'bg-red-100' : 'bg-white') }}">
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
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10 {{ ($manualOverride ? $overrideResult : $isSuccess) ? 'bg-green-100' : 'bg-red-100' }}">
                                @if(($manualOverride ? $overrideResult : $isSuccess))
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
                                    class="text-lg leading-6 font-medium {{ ($manualOverride ? $overrideResult : $isSuccess) ? 'text-green-900' : 'text-red-900' }}">
                                    Processing Results
                                </h3>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div class="bg-white p-4 rounded-lg shadow">
                                <div class="text-sm font-medium text-gray-500">Result</div>
                                <div
                                    class="mt-1 text-2xl font-semibold {{ ($manualOverride ? $overrideResult : $isSuccess) ? 'text-green-600' : 'text-red-600' }}">
                                    {{ ($manualOverride ? $overrideResult : $isSuccess) ? 'Success' : 'Failed' }}
                                </div>
                            </div>
                            <div class="bg-white p-4 rounded-lg shadow">
                                <div class="text-sm font-medium text-gray-500">Success Rate</div>
                                <div class="mt-1 text-2xl font-semibold">{{ $successRate }}%</div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <button type="button" wire:click="toggleOverride"
                                        class="flex items-center focus:outline-none">
                                        <span
                                            class="relative inline-block w-10 h-5 transition duration-200 ease-in-out rounded-full {{ $manualOverride ? 'bg-green-500' : 'bg-gray-300' }}">
                                            <span
                                                class="absolute inset-0 flex items-center justify-{{ $manualOverride ? 'end' : 'start' }}">
                                                <span
                                                    class="w-4 h-4 transition duration-200 ease-in-out transform bg-white rounded-full shadow-md translate-x-{{ $manualOverride ? '5' : '1' }}"></span>
                                            </span>
                                        </span>
                                    </button>
                                    <span class="ml-2 text-sm font-medium text-gray-700">Override Result</span>
                                </div>
                                @if($manualOverride)
                                <div class="flex items-center space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="overrideResult" wire:click="updateOverrideResult(1)"
                                            {{ $overrideResult ? 'checked' : '' }}
                                            class="text-green-600 focus:ring-green-500 h-4 w-4">
                                        <span class="ml-2 text-sm text-green-700">Success</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="overrideResult" wire:click="updateOverrideResult(0)"
                                            {{ !$overrideResult ? 'checked' : '' }}
                                            class="text-red-600 focus:ring-red-500 h-4 w-4">
                                        <span class="ml-2 text-sm text-red-700">Failed</span>
                                    </label>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4">
                            <label for="note" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea id="note" wire:model="note" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Add any observations or notes about this treatment..."></textarea>
                        </div>

                        <div class="mt-2">
                            <p
                                class="text-sm {{ ($manualOverride ? $overrideResult : $isSuccess) ? 'text-green-700' : 'text-red-700' }}">
                                @if($manualOverride)
                                {{ $overrideResult ? 'You have manually marked this treatment as successful.' : 'You
                                have manually marked this treatment as failed.' }}
                                @else
                                {{ $isSuccess ? 'The process was successful with the given parameters.' : 'The process
                                was unsuccessful with the given parameters. Consider adjusting your inputs.' }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm {{ ($manualOverride ? $overrideResult : $isSuccess) ? 'bg-green-600 ' : 'bg-black' }}"
                        wire:click="save">
                        Save
                    </button>
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
