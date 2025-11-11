<?php

namespace Database\Seeders;

use App\Models\Exhibition;
use App\Models\Image;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
            // Jede Exhibition bekommt zwischen 5 und 7 Bilder (deutlich weniger für schnelleres Seeding)
            $imageCount = rand(5, 7);
            
            // Erstelle verschiedene Arten von Bildern
            $images = collect();
            
            // 1 StartSeiteSlide Bild
            $image = Image::factory()->startPageSlide()->make([
                'exhibition_id' => $exhibition->id
            ]);
            $this->downloadImageFromLoremPicsum($image, 1920, 1080); // Große Bilder für Slides
            $images->push($image);
            
            // 1 Header Bild
            $image = Image::factory()->withPosition('Header')->make([
                'exhibition_id' => $exhibition->id
            ]);
            $this->downloadImageFromLoremPicsum($image, 1920, 600); // Breite Header-Bilder
            $images->push($image);
            
            // Rest sind Galerie-Bilder
            $remainingCount = $imageCount - 2; // 2 Bilder bereits erstellt
            for ($i = 0; $i < $remainingCount; $i++) {
                $positions = ['Galerie', 'Detail', 'Vorschau', 'Press'];
                $position = $positions[array_rand($positions)];
                
                $imageData = [
                    'exhibition_id' => $exhibition->id,
                    'position' => $position
                ];
                
                $image = Image::factory()->make($imageData);
                
                // Verschiedene Auflösungen je nach Position
                $width = $position === 'Detail' ? 800 : 1200;
                $height = $position === 'Detail' ? 600 : 800;
                $this->downloadImageFromLoremPicsum($image, $width, $height);
                
                $images->push($image);
            }
            
            // Speichere alle Bilder für diese Exhibition
            $exhibition->images()->saveMany($images);
        }
    }

    /**
     * Download image from Lorem Picsum and save to storage
     */
    private function downloadImageFromLoremPicsum($image, $width = 1200, $height = 800)
    {
        try {
            $imageId = rand(1, 1000); // Lorem Picsum hat über 1000 Bilder
            $imageUrl = "https://picsum.photos/{$width}/{$height}?random={$imageId}";
            
            $imageContent = Http::timeout(10)->get($imageUrl)->body();
            
            $filename = 'images/' . uniqid() . '.jpg';
            
            // Speichere direkt im public Ordner statt storage
            file_put_contents(public_path($filename), $imageContent);

            // Setze den Pfad im Image Model (ohne 'storage/' prefix)
            $image->path = $filename;
            
        } catch (\Exception $e) {
            Log::error('Fehler beim Herunterladen des Bildes: ' . $e->getMessage());
            // Fallback: Verwende einen lokalen Placeholder
            $this->createPlaceholderImage($image, $width, $height);
        }
    }

    /**
     * Create a placeholder image as fallback
     */
    private function createPlaceholderImage($image, $width, $height)
    {
        try {
            $placeholderUrl = "https://via.placeholder.com/{$width}x{$height}/cccccc/333333?text=Exhibition+Image";
            $imageContent = Http::timeout(10)->get($placeholderUrl)->body();
            
            $filename = 'images/placeholder_' . uniqid() . '.jpg';
            file_put_contents(public_path($filename), $imageContent);
            
            $image->path = $filename;
        } catch (\Exception $e) {
            Log::error('Fehler beim Erstellen des Placeholder-Bildes: ' . $e->getMessage());
            // Als letzte Möglichkeit: Dummy-Pfad setzen
            $image->path = 'images/dummy.jpg';
        }
    }
}
