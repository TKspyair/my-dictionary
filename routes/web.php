<?php


use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;



// ルートURL '/' にリクエストがきたときの処理
Route::get('/', function () {
    
    // ユーザーが認証済みかどうかをチェック
    if (Auth::check()) {
        return redirect()->route('word-index');
    } else {
        return redirect()->route('login');
    }
});


Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


//web.php内部でauth.phpを読み込むことで、web.phpに適用されているミドルウェアなどを適用させる
require __DIR__ . '/auth.php';
/*
「require」 : PHPの命令。 機能 → (指定したファイルの有無) ? (ファイルを読み込み、PHPコードを実行) : (Fatal Errorを発生させ、プログラムを停止する)
「__DIR__」 : PHPの「マジック定数」。 指定したファイルが保存されているディレクトリのフルパスに置き換わる(パスの記述の省略ができる)　
　#例　/auth.php →　C:\xampp\htdocs\my-dictionary\routes(その後に指定したいファイルのパスを手動入力)
 */

