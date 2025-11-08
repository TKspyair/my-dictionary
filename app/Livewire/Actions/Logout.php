<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Logout
{
    public function __invoke(): void
    {
        /** 
         * __invoke(): マジックメソッドの一種で、オブジェクトを関数として呼び出せるようになる
         * 例: 
         * 
         * Auth::guard(''): Laravelの認証コンポーネント(Illuminate\Support\Facades\Auth)が管理する複数の認証ドライバから使用するドライバを選択する
         * ※認証ドライバ: 認証ロジックを実装し、認証状態の管理をする仕組みのこと
         * 'web'の定義: my-dictionary\config\auth.php
         * > config('ファイル名.キー.サブキー...')で階層を「.」で繋いで設定値を取得　※config配下の設定ファイルの値はすべてconfig()で取得可能
         * 例: config('auth.defaults.guard') 戻り値: 'web'
         */
        Auth::guard('web')->logout();

        # セッションの無効化
        Session::invalidate();

        # 新しいCSRF(クロスサイトリクエストフォージェリ)トークンを生成
        Session::regenerateToken();
    }
}
