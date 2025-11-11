<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt; 


# 'auth' ユーザーが認証済みの場合のルート定義
# my-dictionary\app\Http\Middleware\Authenticate.php
/**
 * 未認証  　→　新規登録ページ('register')にリダイレクト ※my-dictionary\app\Http\Middleware\Authenticate.phpに定義
 * 認証済み　→　HTTPリクエストと合致する以下のルートにリダイレクト
 * ※認証チェック自体はMiddlewareクラスに定義　Illuminate\Auth\Middleware
 */
Route::middleware('auth')->group(function () {
    Volt::route('word-index', 'pages.words.index')
        ->name('word-index'); 

    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');
});

# guest: ユーザーが未認証の場合のルート定義
# my-dictionaru\app\Http\Middleware\RedirectIfAuthenticated::class)
Route::middleware('guest')->group(function () {

    # 未認証の場合のルートページ
    # 新規登録・ログインページ
    Volt::route('/', 'pages.auth.register-login')
        ->name('register-login');
        
    # パスワードを忘れたページ
    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    # パスワードリセットページ
    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');

    //-----------------------------------------------------
    // テスト用ルーティング: モーダルとして呼び出すページだが、機能テスト用にルート定義する
    //-----------------------------------------------------
    # 新規登録ページ(テスト用)
    Volt::route('register', 'pages.auth.register')
        ->name('register');

    # ログインページ(テスト用)
    Volt::route('login', 'pages.auth.login')
        ->name('login');
});
