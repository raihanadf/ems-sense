<?php

use App\Models\Species;
use Livewire\Attributes\On;


use Livewire\Volt\Component;
use Illuminate\Support\Facades\Http;

new class extends Component {
    public $isRetraining = false;
    public $retrainingOutput = '';
    public $hasError = false;
    public $showOutput = false;

    public function retrain()
    {
        $this->isRetraining = true;
        $this->showOutput = true;

        try {
            $response = Http::get('http://127.0.0.1:8000/retrain-model');
            $data = $response->json();

            if ($response->successful()) {
                $this->retrainingOutput = $data['details'] ?? $data['message'];
                $this->hasError = false;
                $this->dispatch('retrain-success');
            } else {
                $this->retrainingOutput = $data['error'] ?? 'An unknown error occurred';
                $this->hasError = true;
            }
        } catch (\Exception $e) {
            $this->retrainingOutput = "Failed to connect to the retraining service.";
            $this->hasError = true;
        } finally {
            $this->isRetraining = false;
        }
    }

    #[On('retrain-success')]
    public function getSpecies()
    {
        try {
            // get species from API
            $response = Http::get('http://127.0.0.1:8000/species');

            if ($response->successful()) {
                $species = $response->json();

                // loop through the species from the API
                foreach ($species as $speciesName) {
                    // check if the species already exists in the database
                    if (!Species::where('name', $speciesName)->exists()) {
                        // insert the species if it does not exist
                        Species::create(['name' => $speciesName]);
                    }
                }

                $this->syncMessage = 'Species list successfully synchronized.';
            } else {
                throw new \Exception('Failed to fetch species from API');
            }
        } catch (\Exception $e) {
            $this->syncMessage = 'Failed to synchronize species list: ' . $e->getMessage();
            $this->hasError = true;
        }
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Retrain Model') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __("Retrain the model with the latest dataset to improve predictions.") }}
        </p>
    </header>

    <div class="mt-6">
        <div class="flex items-center gap-4">
            <x-primary-button wire:click="retrain" wire:loading.attr="disabled" class="flex items-center gap-2">
                <span wire:loading.remove>{{ __('Retrain Model') }}</span>
                <span wire:loading>{{ __('Retraining...') }}</span>
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

        @if($showOutput)
        <div class="mt-4 p-4 rounded-lg {{ $hasError ? 'bg-red-50' : 'bg-gray-50' }}">
            <div class="flex items-start mb-2">
                <div class="flex-shrink-0">
                    @if($hasError)
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" />
                    </svg>
                    @else
                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
                    </svg>
                    @endif
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium {{ $hasError ? 'text-red-800' : 'text-green-800' }}">
                        {{ $hasError ? 'Retraining Failed' : 'Retraining Succeeded' }}
                    </h3>
                </div>
            </div>
            <div class="ml-8">
                <pre
                    class="mt-2 text-sm whitespace-pre-wrap font-mono {{ $hasError ? 'text-red-700' : 'text-gray-700' }}">
                    {{ $retrainingOutput }}</pre>
            </div>
        </div>
        @endif
    </div>
</section>
