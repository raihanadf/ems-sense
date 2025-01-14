<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Look at Stats!') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="container mx-auto px-4 py-8">

                        <div class="flex flex-wrap -mx-2">
                            <!-- Species Overview -->
                            <div class="w-full md:w-1/2 lg:w-1/3 px-2 mb-4">
                                <div class="bg-white p-6 rounded-lg shadow-md h-full">
                                    <h2 class="text-xl font-semibold mb-4 text-gray-700">Species Overview</h2>
                                    <div class="space-y-2">
                                        <p><span class="font-medium">Total Species:</span> 15</p>
                                        <p><span class="font-medium">Overall Success Rate:</span> 78%</p>
                                        <p><span class="font-medium">Active Treatments:</span> 5</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Results -->
                            <div class="w-full md:w-1/2 lg:w-1/3 px-2 mb-4">
                                <div class="bg-white p-6 rounded-lg shadow-md h-full">
                                    <h2 class="text-xl font-semibold mb-4 text-gray-700">Recent Results</h2>
                                    <ul class="space-y-2">
                                        <li class="flex justify-between items-center">
                                            <span>Species A</span>
                                            <span class="text-green-500 font-medium">Success</span>
                                        </li>
                                        <li class="flex justify-between items-center">
                                            <span>Species B</span>
                                            <span class="text-red-500 font-medium">Failure</span>
                                        </li>
                                        <li class="flex justify-between items-center">
                                            <span>Species C</span>
                                            <span class="text-green-500 font-medium">Success</span>
                                        </li>
                                        <li class="flex justify-between items-center">
                                            <span>Species D</span>
                                            <span class="text-green-500 font-medium">Success</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Parameters Summary -->
                            <div class="w-full md:w-1/2 lg:w-1/3 px-2 mb-4 md:mt-8 lg:mt-0">
                                <div class="bg-white p-6 rounded-lg shadow-md h-full">
                                    <h2 class="text-xl font-semibold mb-4 text-gray-700">Parameters Summary</h2>
                                    <div class="space-y-2">
                                        <p><span class="font-medium">Concentration Range:</span> 10-50%</p>
                                        <p><span class="font-medium">Temperature Range:</span> 20-30°C</p>
                                        <p><span class="font-medium">Duration Range:</span> 30-120 min</p>
                                        <p><span class="font-medium">Most Common Concentration:</span> 35%</p>
                                        <p><span class="font-medium">Optimal Temperature:</span> 25°C</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Top Metrics -->
                            <div class="w-full md:w-1/2 lg:w-1/3 px-2 mb-4 md:mt-8 lg:mt-0">
                                <div class="bg-white p-6 rounded-lg shadow-md h-full">
                                    <h2 class="text-xl font-semibold mb-4 text-gray-700">Top Metrics</h2>
                                    <div class="space-y-2">
                                        <p><span class="font-medium">Avg. Success Rate:</span> 78%</p>
                                        <p><span class="font-medium">Most Treated:</span> Species X (52 treatments)</p>
                                        <p><span class="font-medium">Best Condition:</span> 25°C, 40% concentration</p>
                                        <p><span class="font-medium">Fastest Treatment:</span> 45 minutes (Species Y)
                                        </p>
                                        <p><span class="font-medium">Highest Success Rate:</span> 92% (Species Z)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
