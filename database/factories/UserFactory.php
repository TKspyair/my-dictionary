<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class UserFactory extends Factory
{
    
    protected static ?string $password;

    # ダミーデータの生成ロジック
    public function definition(): array
    {
        /**
         ** static::$password ??=: UserFactoryクラスの初期読み込み時に、一回だけ右辺の値が代入され、クラス由来のインスタンスで共有する
         * static::$property: 静的プロパティ、クラスが初期読み込みされたとき一回だけメモリに確保され、そのクラスから作成されるすべてのインスタンス間で共有される
         *  ??= : Null合体代入演算子、変数に値が設定されていないときのみ右辺の値を代入する
         */
        return [
            // idカラムは自動生成されるので設定不要
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    # ファクトリから生成されるUserインスタンスを「メールアドレス未認証」の状態にする
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
