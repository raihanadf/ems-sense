<?php

namespace Database\Seeders;

use App\Models\Species;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class SpeciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->getSpecies();
    }

    public function getSpecies(): void
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
            } else {
                throw new \Exception('Failed to fetch species from API');
            }
        } catch (\Exception $e) {
            print_r("\n\n\n !!! Make sure your API is running !!! \n\n\n");
            throw $e;
        }
    }
}
