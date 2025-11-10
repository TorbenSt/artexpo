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
        // Erstelle 10 Exhibitions
        Exhibition::factory()
            ->count(10)
            ->create();
    }
}
