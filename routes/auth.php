<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt; // 単一ファイルでLivewireコンポーネントから直接ルートを定義できる

Route::middleware('guest')->group(function () {

    Volt::route('/', 'pages.auth.login')
        ->name('login');
    /*
    Volt::route('URI', 'Livewireコンポーネント名'): Livewire コンポーネントを特定の URI に紐づける
    ->name('ルート名')：route()を使って、このルート名でURLを生成できる(URLが変更されてもルートを書き換える必要がない)
    */

    Volt::route('register', 'pages.auth.register')
        ->name('register');
        
    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');

    Volt::route('word-index', 'pages.words.index')
        ->name('word-index'); 
});
