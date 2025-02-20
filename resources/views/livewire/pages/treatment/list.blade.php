<?php
use App\Models\Treatment;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    public Collection $treatments;
    public $showNoteModal = false;
    public $selectedTreatment = null;

    public function mount()
    {
        $this->getLatestTreatments();
    }

    #[On('treatment-created')]
    public function getLatestTreatments()
    {
        $this->treatments = Treatment::where('user_id', auth()->user()->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    public function showNote($treatmentId)
    {
        $this->selectedTreatment = $this->treatments->firstWhere('id', $treatmentId);
        if ($this->selectedTreatment) {
            $this->showNoteModal = true;
        }
    }

    public function closeNoteModal()
    {
        $this->showNoteModal = false;
        $this->selectedTreatment = null;
    }
}; ?>

<div class="mx-auto mt-8 p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-xl font-bold mb-4">Your last 10 predictions</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-700">Species</th>
                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-700">Concentration</th>
                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-700">Soak Duration</th>
                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-700">Lowest Temp</th>
                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-700">Highest Temp</th>
                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-700">Date</th>
                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-700">Results</th>
                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-700">Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($treatments as $treatment)
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-4 border-b text-sm">{{ $treatment->species->name }}</td>
                    <td class="py-2 px-4 border-b text-sm">{{ $treatment->emsConcentration }}%</td>
                    <td class="py-2 px-4 border-b text-sm">{{ $treatment->soakDuration }} min</td>
                    <td class="py-2 px-4 border-b text-sm">{{ $treatment->lowestTemp }}°C</td>
                    <td class="py-2 px-4 border-b text-sm">{{ $treatment->highestTemp }}°C</td>
                    <td class="py-2 px-4 border-b text-sm">{{ $treatment->created_at->format('Y-m-d') }}</td>
                    <td class="py-2 px-4 border-b text-sm">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            {{ $treatment->result ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $treatment->result ? 'Success' : 'Failure' }}
                        </span>
                    </td>
                    <td class="py-2 px-4 border-b text-sm">
                        <button wire:click="showNote({{ $treatment->id }})"
                            class="text-gray-500 hover:text-indigo-600 transition-colors focus:outline-none"
                            title="View Notes">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path
                                    d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                            </svg>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Simplified Note Modal -->
    @if($showNoteModal && $selectedTreatment)
    <div class="fixed inset-0 z-10 overflow-y-auto bg-gray-500 bg-opacity-75 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4">
            <div class="p-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0 bg-indigo-100 p-2 rounded-full">
                        <svg class="h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-lg font-medium text-gray-900">Treatment Notes</h3>
                        <div class="mt-3 space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="font-medium text-gray-500">Species:</span>
                                <span>{{ $selectedTreatment->species->name }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="font-medium text-gray-500">Date:</span>
                                <span>{{ $selectedTreatment->created_at->format('Y-m-d') }}</span>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-md">
                                @if($selectedTreatment->note)
                                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $selectedTreatment->note }}</p>
                                @else
                                <p class="text-sm italic text-gray-500">No notes were added for this treatment.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-3 flex justify-end rounded-b-lg">
                <button type="button"
                    class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    wire:click="closeNoteModal">
                    Close
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
