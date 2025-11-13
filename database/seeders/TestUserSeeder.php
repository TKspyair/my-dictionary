<?php

namespace Database\Seeders;

use App\Models\User; //このSeederで作成するモデルを定義
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    
    public function run(): void
    {

        $email = 'test@example.com';

        # テストユーザーが存在する場合、Seederの処理をスキップ
        /** $this->command->[メソッド]:　ターミナルに指定のメッセージを出力する、以下のメソッドにより出力を調整できる
         *  > info(): 緑色, error(): 赤色など意味に基づいた色付けや書式を自動で行う意
         */
        if(User::where('email', $email)->exists())
        {
            $this->command->info("テストユーザーは既に存在するため、スキップしました。");
            return; // 処理をここで終了
        }

        # テストユーザーのデータを1名分作成
        /** User::factory()->create(); :ランダムなデータをもつユーザーの作成
         * factory():  指定されたモデルのファクトリを実行、引数に数値を入れるとその数だけダミーデータを作成
         * create(): 引数に連想配列でキーと値を指定することで、生成されるダミーデータの値を固定できる
         * ※Models/User.phpに定義された$filable(カラムへの一括代入の制限)は関係なく、データを作成できる
         * $fillable: アプリケーションの外部からの入力に対してのみ、カラムへの一括代入の制限をする
         */
        User::factory()->create([
            'email' => 'test@example.com', 
            'password' => Hash::make('password'), 
            'email_verified_at' => now(), 
        ]);
    }
}
