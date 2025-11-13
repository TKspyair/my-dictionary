<?php
/** use [対応するモデルのパス]は不要
 * > Laravelはファクトリのクラス名から対応するモデルを自動で推論する　条件: ファクトリ名 = [モデル名] + [Factory]
 * 
 */
namespace Database\Factories;


use Illuminate\Database\Eloquent\Factories\Factory;


class TagFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tag_name' => fake()->unique()->word(),
        ];
    }
}
