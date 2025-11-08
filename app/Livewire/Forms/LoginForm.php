<?php

namespace App\Livewire\Forms;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
//======================================================================
// プロパティ
//======================================================================
    # メールアドレス
    #[Validate('required|string|email')]
    public string $email = '';

    # パスワード
    #[Validate('required|string')]
    public string $password = '';

    # パスワードの記憶
    #[Validate('boolean')]
    public bool $remember = false;


//======================================================================
// メソッド
//======================================================================

    # 認証ロジック
    public function authenticate(): void
    {
        # ログイン試行回数の制限チェック
        $this->ensureIsNotRateLimited();

        # ユーザー認証の実行
        if (! Auth::attempt($this->only(['email', 'password']), $this->remember)) {
            # 認証失敗時、試行回数を一回増加
            RateLimiter::hit($this->throttleKey()); //throttle(動詞): 制限・調整する　※認証系システムにおいてはログイン試行回数の制限によく使われる

            # 認証失敗メッセージを発生させる
            throw ValidationException::withMessages([
                'form.email' => trans('auth.failed'),
            ]);
        }

        # 認証成功時、試行回数をリセット
        RateLimiter::clear($this->throttleKey());
    }

    # ログインのレート制限の確認　※一定の期間内にある操作を実行できる回数に上限を設ける仕組み
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        # ロックアウトイベントの発火
        event(new Lockout(request()));

        # ロックアウト解除の残り時間を取得
        $seconds = RateLimiter::availableIn($this->throttleKey());

        # ロックアウトメッセージを発生させる
        throw ValidationException::withMessages([
            'form.email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    # レート制限の試行回数を追跡するキーを生成
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}
