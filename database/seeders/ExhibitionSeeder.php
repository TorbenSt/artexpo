<?php

namespace Database\Seeders;

use App\Models\Exhibition;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExhibitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Erstelle nur 3 Exhibitions für schnelleres Seeding
        Exhibition::factory()
            ->count(3)
            ->create();
    }
}
