<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Exhibition>
 */
class ExhibitionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+2 years');
        $endDate = fake()->dateTimeBetween($startDate, $startDate->format('Y-m-d') . ' +6 months');

        return [
            'title' => fake()->sentence(3, false),
            'subtitle' => fake()->optional(0.7)->sentence(4, false),
            'intro_text' => fake()->optional(0.8)->paragraph(3),
            'text' => fake()->optional(0.9)->paragraphs(5, true),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'artist' => fake()->optional(0.8)->name(),
            'program_booklet' => fake()->optional(0.6)->url(),
            'program_booklet_cover' => fake()->optional(0.6)->imageUrl(300, 400, 'documents'),
            'flyer' => fake()->optional(0.7)->url(),
            'flyer_cover' => fake()->optional(0.7)->imageUrl(300, 400, 'flyer'),
            'creative_booklet' => fake()->optional(0.5)->url(),
            'creative_booklet_cover' => fake()->optional(0.5)->imageUrl(300, 400, 'books'),
            'ticket_link' => fake()->optional(0.8)->url(),
        ];
    }
}
