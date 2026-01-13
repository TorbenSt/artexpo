<?php

namespace Database\Seeders;

use App\Models\RagSource;
use Illuminate\Database\Seeder;

class RagSourcesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sources = [
            [
                'name' => 'Wikipedia (de)',
                'base_url' => 'https://de.wikipedia.org/wiki/',
                'license_note' => 'CC-BY-SA 3.0 / CC-BY-SA 4.0',
                'is_allowed' => true,
            ],
            [
                'name' => 'Wikipedia (en)',
                'base_url' => 'https://en.wikipedia.org/wiki/',
                'license_note' => 'CC-BY-SA 3.0 / CC-BY-SA 4.0',
                'is_allowed' => true,
            ],
            [
                'name' => 'Wikidata',
                'base_url' => 'https://www.wikidata.org/wiki/',
                'license_note' => 'CC0 1.0',
                'is_allowed' => true,
            ],
            [
                'name' => 'Metropolitan Museum API',
                'base_url' => 'https://www.metmuseum.org/art/collection/',
                'license_note' => 'CC0 1.0 (Public Domain)',
                'is_allowed' => true,
            ],
            [
                'name' => 'Wikimedia Commons',
                'base_url' => 'https://commons.wikimedia.org/wiki/',
                'license_note' => 'Various CC Licenses',
                'is_allowed' => true,
            ],
        ];

        foreach ($sources as $source) {
            RagSource::firstOrCreate(
                ['name' => $source['name']],
                $source
            );
        }
    }
}
