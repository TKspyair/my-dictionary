<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Word;
use App\Models\User;

class WordSeeder extends Seeder
{
    public function run(): void
    {
        # テストユーザーの取得
        $testUser = User::where('email', 'test@example.com')->first();
        
        # テストユーザーが存在しない場合、処理を中断
        if (!$testUser) {
            echo "テストユーザーが見つかりません: test@example.com\n";
            return;
        }

        # テストユーザーに紐づけてタグのダミーデータを作成
        Word::factory(100)->create([
            'user_id' => $testUser->id,
        ]);

        $this->command->info("テストユーザー ({$testUser->email}) に20件のWordデータを作成しました。\n");
    }
}
