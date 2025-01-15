<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Raihan',
            'email' => 'r@r.com',
            'password' => '$2y$12$T7yfjVsZLLbsQVYAd4s5tumQoDWpImOB.iwaZ4BDy.mYdNOZfXyQ.',
            'role' => 'curator',
        ]);

        User::factory()->create([
            'name' => 'Irfan',
            'email' => 'i@i.com',
            'password' => '$2y$12$T7yfjVsZLLbsQVYAd4s5tumQoDWpImOB.iwaZ4BDy.mYdNOZfXyQ.',
        ]);

        $this->call([
            SpeciesSeeder::class,
        ]);
    }
}
