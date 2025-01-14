<?php

use App\Models\Treatment;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Http;

new class extends Component {
    public $isExporting = false;
    public $exportMessage = '';
    public $hasError = false;

    private function getAllTreatments()
    {
        try {
            // Get treatments from API
            $response = Http::get('http://127.0.0.1:8000/get-csv');

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch existing treatments from API');
            }

            $apiTreatments = collect($response->json());

            // Get treatments from database
            $dbTreatments = Treatment::with('species')
                ->get()
                ->map(function ($treatment) {
                    return [
                        'species' => $treatment->species->name,
                        'emsConcentration' => $treatment->emsConcentration,
                        'soakDuration' => $treatment->soakDuration,
                        'lowestTemp' => $treatment->lowestTemp,
                        'highestTemp' => $treatment->highestTemp,
                        'result' => $treatment->result,
                    ];
                });

            // Merge and remove duplicates
            return $apiTreatments->concat($dbTreatments)
                ->unique(function ($item) {
                    // Create a unique key based on all values to identify duplicates
                    return sprintf(
                        '%s-%.2f-%d-%.1f-%.1f-%d',
                        $item['species'],
                        $item['emsConcentration'],
                        $item['soakDuration'],
                        $item['lowestTemp'],
                        $item['highestTemp'],
                        $item['result']
                    );
                })
                ->values();

        } catch (\Exception $e) {
            throw new \Exception('Failed to merge treatments: ' . $e->getMessage());
        }
    }

    public function exportAndUpload()
    {
        $this->isExporting = true;
        $this->hasError = false;

        try {
            // Get merged treatments
            $treatments = $this->getAllTreatments();

            // Create CSV content
            $csvContent = "species,emsConcentration,soakDuration,lowestTemp,highestTemp,result\n";

            foreach ($treatments as $treatment) {
                $csvContent .= sprintf(
                    "%s,%.2f,%d,%.1f,%.1f,%d\n",
                    $treatment['species'],
                    $treatment['emsConcentration'],
                    $treatment['soakDuration'],
                    $treatment['lowestTemp'],
                    $treatment['highestTemp'],
                    $treatment['result']
                );
            }

            // Create temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'treatments_');
            file_put_contents($tempFile, $csvContent);

            // Upload the file
            $response = Http::attach(
                'file',
                file_get_contents($tempFile),
                'treatments.csv'
            )->post('http://127.0.0.1:8000/upload-csv');

            unlink($tempFile); // Clean up temporary file

            if ($response->successful()) {
                $this->exportMessage = 'Treatments exported and uploaded successfully!';
            } else {
                throw new \Exception($response->json()['error'] ?? 'Failed to upload file');
            }
        } catch (\Exception $e) {
            $this->exportMessage = 'Failed to export and upload treatments: ' . $e->getMessage();
            $this->hasError = true;
        } finally {
            $this->isExporting = false;
        }
    }
}; ?>


<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Sync and Merge Training Data') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __("Merging verified predictions record with training data to improve predictions on retraining.") }}
        </p>
    </header>

    <div class="mt-6">
        <x-primary-button wire:click="exportAndUpload" wire:loading.attr="disabled" class="flex items-center gap-2">
            <span wire:loading.remove>{{ __('Sync and Merge Treatments') }}</span>
            <span wire:loading>{{ __('Exporting...') }}</span>
            <span wire:loading class="inline-block">
                <svg class="animate-spin h-4 w-4 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </span>
        </x-primary-button>

        @if($exportMessage)
        <div class="mt-4 p-4 rounded-lg {{ $hasError ? 'bg-red-50' : 'bg-green-50' }}">
            <div class="flex items-start">
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
                    <p class="text-sm {{ $hasError ? 'text-red-700' : 'text-green-700' }}">
                        {{ $exportMessage }}
                    </p>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>
