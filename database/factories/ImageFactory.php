<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Image>
 */
class ImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['public', 'press']);
        $imageName = $this->faker->word() . '_' . $this->faker->randomNumber(4) . '.jpg';
        
        return [
            'exhibition_id' => \App\Models\Exhibition::factory(),
            'type' => $type,
            'path' => 'images/resized/' . $imageName,
            'original_path' => $type === 'press' ? 'images/original/' . $imageName : null,
            'credits' => $this->faker->optional(0.7)->name(),
            'visible' => $this->faker->boolean(85), // 85% Wahrscheinlichkeit für true
            'position' => $this->faker->optional(0.6)->randomElement([
                'StartSeiteSlide',
                'Galerie',
                'Header',
                'Vorschau',
                'Detail'
            ]),
        ];
    }

    /**
     * Indicate that the image is of type 'public'.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'public',
            'original_path' => null,
        ]);
    }

    /**
     * Indicate that the image is of type 'press'.
     */
    public function press(): static
    {
        return $this->state(function (array $attributes) {
            $imageName = $this->faker->word() . '_' . $this->faker->randomNumber(4) . '.jpg';
            return [
                'type' => 'press',
                'original_path' => 'images/original/' . $imageName,
            ];
        });
    }

    /**
     * Indicate that the image is not visible.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'visible' => false,
        ]);
    }

    /**
     * Set a specific position for the image.
     */
    public function withPosition(string $position): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => $position,
        ]);
    }

    /**
     * Image for start page slide.
     */
    public function startPageSlide(): static
    {
        return $this->withPosition('StartSeiteSlide');
    }

    /**
     * Image for gallery.
     */
    public function gallery(): static
    {
        return $this->withPosition('Galerie');
    }
}
