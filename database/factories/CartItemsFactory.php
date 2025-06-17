<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CartItems>
 */
class CartItemsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),                                               // Gera uma palavra aleatória (ex.: "Caneta", "Notebook")
            'price' => fake()->randomFloat(2, 1, 1000),    // Preço entre 1.00 e 1000.00 (2 casas decimais)
            'quantity' => fake()->numberBetween(1, 100),                // Quantidade entre 1 e 100
            'created_at' => now(),
        ];
    }
}
