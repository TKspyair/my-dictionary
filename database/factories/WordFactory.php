<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;


class WordFactory extends Factory
{

    public function definition(): array
    {
        # ランダムな語句とその説明文を作成
        return [
            'word_name' => fake()->unique()->word(),
            'description' => fake()->text(100),
        ];
    }
}
