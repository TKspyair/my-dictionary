<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\User; //テストユーザーの取得
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    # タグを100件作成
    public function run(): void
    {
        # テストユーザーの取得
        $testUser = User::where('email', 'test@example.com')->first();
        
        if (!$testUser) {
            echo "テストユーザーが見つかりません: test@example.com\n";
            return;
        }

        # テストユーザーに紐づけてタグのダミーデータを作成
        Tag::factory(20)->create([
            'user_id' => $testUser->id,
        ]);

        $this->command->info("テストユーザー ({$testUser->email}) に20件のTagデータを作成しました。\n");
    }
}
