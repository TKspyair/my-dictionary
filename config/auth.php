<?php

/** config/auth.php: 認証システム全体を構成する設定ファイル
 * config('ファイル名.キー.サブキー...')で設定値を取得　※config配下の設定ファイルの値はすべてconfig()で取得可能
 * 例: config('auth.defaults.guard') 戻り値: 'web'
 * ※ファイルの内容がreturnから始まる配列や値を返す書き方を「設定ファイル」という。
 * > PHPフレームワークでは設定データを定義する標準的な方法
 */
return [

    
    /**
     * ガード名を指定しない場合のデフォルトの認証ガード
     * 例: Auth:user()
     */
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /**
     * アプリが使用する具体的な認証ドライバを定義
     * Auth::guard('web')で参照される
     * 'driver': ユーザーの認証状態の管理方法を定義している
     * 'provider': ユーザーアカウント情報へのアクセス方法を定義している
     * 
     */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /**
     * 認証時のユーザープロバイダの定義
     * ※ユーザープロバイダ: ユーザーアカウントの情報を取得する方法を定義するコンポーネント
     * 
     */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /**
     * パスワードリセットの設定
     */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /**
     * パスワード確認のタイムアウト
     */

    'password_timeout' => 10800,

];
