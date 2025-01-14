<?php
use App\Models\Treatment;
use App\Models\Species;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Livewire\Volt\Component;

new class extends Component {
    use WithPagination;

    public $selectedSpecies = '';
    public $selectedResult = '';

    public function mount()
    {
        $this->getLatestTreatments();
    }

    public function getSpecies(): Collection
    {
        return Species::orderBy('name')->get();
    }

    #[On('treatment-created')]
    public function getLatestTreatments()
    {
        // reset pagination when new treatment is added
        $this->resetPage();
    }

    public function getTreatments()
    {
        $query = Treatment::query()->with('species')->latest();

        if ($this->selectedSpecies) {
            $query->where('species_id', $this->selectedSpecies);
        }

        if ($this->selectedResult !== '') {
            $query->where('result', $this->selectedResult === 'success');
        }

        return $query->paginate(10);
    }
}; ?>

<div class="mx-auto mt-8 p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-xl font-bold mb-4">All conducted predictions</h2>

    <!-- Filters -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="species" class="block text-sm font-medium text-gray-700 mb-1">Filter by Species</label>
            <select wire:model.live="selectedSpecies" id="species" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All Species</option>
                @foreach($this->getSpecies() as $species)
                    <option value="{{ $species->id }}">{{ $species->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="result" class="block text-sm font-medium text-gray-700 mb-1">Filter by Result</label>
            <select wire:model.live="selectedResult" id="result" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All Results</option>
                <option value="success">Success</option>
                <option value="failure">Failure</option>
            </select>
        </div>
    </div>

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
                </tr>
            </thead>
            <tbody>
                @foreach($this->getTreatments() as $treatment)
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-4 border-b text-sm">{{ $treatment->species->name }}</td>
                    <td class="py-2 px-4 border-b text-sm">{{ $treatment->emsConcentration }}%</td>
                    <td class="py-2 px-4 border-b text-sm">{{ $treatment->soakDuration }} min</td>
                    <td class="py-2 px-4 border-b text-sm">{{ $treatment->lowestTemp }}°C</td>
                    <td class="py-2 px-4 border-b text-sm">{{ $treatment->highestTemp }}°C</td>
                    <td class="py-2 px-4 border-b text-sm">{{ $treatment->created_at->format('Y-m-d') }}</td>
                    <td class="py-2 px-4 border-b text-sm">
                        @if ($treatment->result)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Success</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Failure</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $this->getTreatments()->links() }}
    </div>
</div>
