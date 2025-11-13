<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
        # 以下のシーダーを実行(テストユーザーを作成)
        $this->call([
            TestUserSeeder::class, 
            WordSeeder::class,
            TagSeeder::class,
        ]);
    }
}
