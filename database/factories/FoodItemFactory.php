<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FoodItem>
 */
class FoodItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'brand' => fake()->company(),
            'barcode' => fake()->unique()->numerify('##########'),
            'serving_size' => fake()->randomElement(['100g', '1 cup', '1 piece', '1 serving']),
            'calories' => fake()->numberBetween(50, 500),
            'protein' => fake()->randomFloat(1, 1, 30),
            'carbohydrates' => fake()->randomFloat(1, 5, 50),
            'fat' => fake()->randomFloat(1, 1, 20),
            'fiber' => fake()->randomFloat(1, 0, 10),
            'sugar' => fake()->randomFloat(1, 0, 25),
            'sodium' => fake()->numberBetween(0, 1000),
            'cholesterol' => fake()->numberBetween(0, 100),
            'image_url' => fake()->imageUrl(),
            'chomp_id' => fake()->unique()->uuid(),
        ];
    }
}
