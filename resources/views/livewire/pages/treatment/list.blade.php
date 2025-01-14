<?php



use App\Models\Treatment;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {

    public Collection $treatments;

    public function mount()
    {
        $this->getLatestTreatments();
    }

    #[On('treatment-created')]
    public function getLatestTreatments()
    {
        $this->treatments = Treatment::where('user_id', auth()->user()->id)->orderBy('created_at', 'desc')->limit(10)->get();
    }

}; ?>

<div class=" mx-auto mt-8 p-6 bg-white rounded-lg shadow-md">
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
                        @if ($treatment->result)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Success</span>
                        @else
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Failure</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
