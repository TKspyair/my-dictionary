
<?php
/** TODO
 * ログアウト機能の追加(ログインページなどの修正が現状難しいため)
 * デザインの修正
*/

use App\Livewire\Forms\LoginForm;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    /** 
     * LoginForm: my-dictionary\app\Livewire\Forms\LoginForm.php 参照
     * 
    */
    public LoginForm $form;

    # ログイン処理
    public function login(): void
    {
        # バリデーションルールはLoginFormクラスに定義
        $this->validate();

        # LoginFormクラスの認証処理
        $this->form->authenticate();

        # セッションIDの生成
        Session::regenerate();

        # 基本ページ(語句一覧ページ)にリダイレクト
        $this->redirect(RouteServiceProvider::HOME, navigate: true);
    }
}; ?>

<div class="container-lg">
    <x-auth-session-status :status="session('status')" />

    <form wire:submit="login">
        <div class="d-flex flex-column justify-content-center">
            <!-- メールアドレス -->
            <div>
                <input wire:model="form.email" type="email" name="email"
                    required autofocus autocomplete="username" placeholder="メールアドレス"/>

                @error('form.email') 
                    <div>{{ $message }}</div> 
                @enderror
            </div>

            <!-- パスワード -->
            <div>
                <input wire:model="form.password" type="password"
                    name="password" required autocomplete="current-password" placeholder="パスワード"/>

                @error('form.password') 
                    <div>{{ $message }}</div> 
                @enderror
            </div>

            <!-- ログイン状態の保持 -->
            <div>
                <label for="remember">
                    <input wire:model="form.remember" id="remember" type="checkbox" name="remember">
                    <span>ログイン状態を保持する</span>
                </label>
            </div>

            <!--  パスワードを忘れた場合-->
            <div>
                <a href="{{ route('password.request') }}" wire:navigate>
                    パスワードを忘れた場合
                </a>
            </div>

            <!-- ボタン群 -->
            <div>
                <button type="button">
                    <a href="{{ route('register') }}" wire:navigate>
                        新規登録
                    </a>
                </button>

                <button type="submit" class="bg-transparent border">
                    ログイン
                </button>
            </div>
        </div>
    </form>
</div>