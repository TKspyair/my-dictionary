<?php

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new #[Layout('layouts.words-app')] class extends Component 
{
//======================================================================
// プロパティ
//======================================================================
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

//======================================================================
// メソッド
//======================================================================
    # 会員登録処理
    public function register(): void
    {
        $validated = $this->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        # 検証済みのパスワードをハッシュ化して再代入
        $validated['password'] = Hash::make($validated['password']);

        /** $user = User::create($validated)
         * > 新しいユーザーレコードを作成し、作成したユーザーレコードをモデルインスタンスとして$userに代入
         ** new Registered(ユーザーモデルインスタンス)
         *
         */
        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        /** navigate: true: リダイレクトの際のフルページリロードを阻止し、DOMの変更された部分のみを更新する
         * ※a要素に使用するwire:navigateと同じ機能(HOTWやTurbo-style-Naviigationという技術でほかの言語にも同様の機能が存在する)
         */
        $this->redirect(RouteServiceProvider::HOME, navigate: true);
    }

    # フォームをクリア
    /** 
     * resetValidation()(Livewire): バリデーションのエラーメッセージをクリアする
     * reset('プロパティ名'): プロパティを初期値にリセットする
    */
    #[On('clear-form-register')]
    public function clearForm()
    {
        $this->reset(['email', 'password', 'password_confirmation']);
        $this->resetValidation();
    }

}; ?>

<section class="d-flex flex-grow flex-column justify-content-center h-100">
    <form wire:submit.prevent="register">
        <div class="d-flex flex-column justify-content-center align-items-center">
            <!-- アプリアイコンの設定(仮) -->
            <div>
                <i class="bi bi-book fs-1"></i>
            </div>

            <!-- メールアドレス -->
            <div class="mt-4 position-relative">
                <x-input-error type="email" wire:model="email" 
                    autofocus autocomplete="username" placeholder="メールアドレス" />
            </div>

            <!-- パスワード -->
            <div class="mt-5 position-relative">
                <x-input-error type="password" wire:model="password"
                    autocomplete="new-password" placeholder="パスワード" />
            </div>

            <!-- パスワード確認 -->
            <div class="mt-5 position-relative">
                <x-input-error type="password" wire:model="password_confirmation"
                    autocomplete="new-password" placeholder="パスワード確認" />
            </div>

            <!-- アカウント作成ボタン -->
            <div class="d-flex justify-content-center mt-5">
                <x-submit-button>
                    {{ __('Register') }}
                </x-submit-button>
            </div>
        </div>
    </form>
</section>
