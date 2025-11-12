<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.words-app')] class extends Component
{
    public string $email = '';

    # パスワード再設定リンクの送信
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        /** Password::sendResetLink(array ['email' => $this->email],[コールバック関数])
         * > 
         * 1 第1引数の'email'(キー)からメールアドレス(値)を取得し、usersテーブルから該当のユーザーを検索する
         * 2 検索成功時、一意で期限付きのリセットトークンを生成
         * 3 生成したトークンとメールアドレス、タイムスタンプをDBのpassword_reset_tokensテーブルに保存する
         * 4 パスワードリセットURLを生成し、そのURLを含むメールをユーザーのメアド宛に送信する
        */
        $status = Password::sendResetLink(
            $this->only('email')
        );

        #　ユーザー検索に失敗時、メールアドレスフィールドにエラーメッセージを関連させる
        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        # メールアドレスフィールドのリセット
        $this->reset('email');

        # 一時的なエラーメッセージを生成する
        session()->flash('status', __($status));
    }
}; ?>

<div class="conteiner-md">
    <div class="d-flex flex-column justify-content-center align-items-center vh-100">
        <!-- アプリアイコンの設定(仮) -->
        <div>
            <i class="bi bi-book fs-1"></i>
        </div>

        <!-- 注意書き -->
        <div class="mt-4">
            <span>ご登録いただいたメールアドレスを入力してください。<br>
                パスワード設定用のURLをメールにて送信いたします。   
            </span>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="m-0 p-0" :status="session('status')" />

        <form wire:submit="sendPasswordResetLink">
            <!-- Email Address -->
            <div class="position-relative mt-4">
                <x-input-error type="e-mail" wire:model="email" autofocus placeholder="メールアドレス"/>
            </div>

            <div class="d-flex justify-content-center mt-5">
                <button type="submit" class="btn btn-primary">
                    {{ __('Email Password Reset Link') }}
                </button>
            </div>
        </form>
    </div>
</div>
