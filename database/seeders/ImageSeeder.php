<?php

namespace Database\Seeders;

use App\Models\Exhibition;
use App\Models\Image;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hole alle Exhibitions
        $exhibitions = Exhibition::all();

        foreach ($exhibitions as $exhibition) {
            // Jede Exhibition bekommt zwischen 20 und 30 Bilder
            $imageCount = rand(20, 30);
            
            // Erstelle verschiedene Arten von Bildern
            $images = collect();
            
            // 1-3 StartSeiteSlide Bilder
            $slideCount = rand(1, 3);
            for ($i = 0; $i < $slideCount; $i++) {
                $images->push(Image::factory()->startPageSlide()->make([
                    'exhibition_id' => $exhibition->id
                ]));
            }
            
            // 2-4 Press Bilder mit Original
            $pressCount = rand(2, 4);
            for ($i = 0; $i < $pressCount; $i++) {
                $images->push(Image::factory()->press()->make([
                    'exhibition_id' => $exhibition->id,
                    'position' => rand(0, 1) ? 'Press' : null
                ]));
            }
            
            // 1-2 Header Bilder
            $headerCount = rand(1, 2);
            for ($i = 0; $i < $headerCount; $i++) {
                $images->push(Image::factory()->withPosition('Header')->make([
                    'exhibition_id' => $exhibition->id
                ]));
            }
            
            // Rest sind Galerie-Bilder
            $remainingCount = $imageCount - $images->count();
            for ($i = 0; $i < $remainingCount; $i++) {
                $isGallery = rand(0, 100) < 70; // 70% Galerie-Bilder
                $position = $isGallery ? 'Galerie' : (rand(0, 1) ? 'Detail' : 'Vorschau');
                
                $imageData = [
                    'exhibition_id' => $exhibition->id,
                    'position' => $position
                ];
                
                // 10% der Bilder sind versteckt
                if (rand(0, 100) < 10) {
                    $images->push(Image::factory()->hidden()->make($imageData));
                } else {
                    $images->push(Image::factory()->make($imageData));
                }
            }
            
            // Speichere alle Bilder für diese Exhibition
            $exhibition->images()->saveMany($images);
        }
    }
}
