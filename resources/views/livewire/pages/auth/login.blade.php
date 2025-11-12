<?php
/** TODO
 * アプリアイコンの設定
 * デザインの修正
 */

use App\Livewire\Forms\LoginForm;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new #[Layout('layouts.words-app')] class extends Component 
{
    # LoginFormクラスのインスタンス化(バリデーションや認証メソッドが使用できるようになる)
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

{{-- モーダル内の要素を中央揃えで配置する方法
**  モーダル呼び出し先の要素
*  <div class="h-100">@livewire('pages.auth.login')</div>
** モーダル内の要素
* <section class="h-100 d-flex flex-grow flex-column justify-content-center"></section>
* h-100: 要素を親要素の高さと同一にする
* d-flex: 要素をFlexboxコンテナとして扱い、以下のクラスを適用できるようにする
* flex-column: Flexboxの主軸を横→縦に変更
* justify-content-center: 要素を垂直方向の中央に配置する
--}}
<section class="h-100 d-flex flex-column justify-content-center">
    <!-- セッションステータス(ログイン成功メッセージなど)を表示する -->
    {{--
    * session('status')の引数を:statusに代入 
    ※「:」はBladeテンプレートにおいて動的な値を設定するための特別な記号 
    --}}
    <x-auth-session-status :status="session('status')" />

    <form wire:submit="login">
        {{-- 
        * flex-column: Flexboxの主軸を垂直方向にする(軸が90度回転する)　
        > 例　 d-flexのみ: justify-content(水平方向の配置) →　flex-column適用: justify-content(垂直方向の配置)

        * vh-100: 親要素の高さを表示画面の大きさに設定
        ※ここではフォームを囲うdiv要素に適用することでフォーム要素を画面中央に配置することに成功している
        --}}
        <div class="d-flex flex-column justify-content-center align-items-center">
            <!-- アプリアイコンの設定(仮) -->
            <div>
                <i class="bi bi-book fs-1"></i>
            </div>

            <!-- メールアドレス -->
            <div class="mt-4 position-relative">
                <x-input-error type="email" wire:model="form.email" autofocus
                    autocomplete="username" placeholder="メールアドレス" />
            </div>

            <!-- パスワード -->
            <div class="mt-5 position-relative">
                <x-input-error type="password" wire:model="form.password"
                    autocomplete="current-password" placeholder="パスワードは8文字以上" />
            </div>

            <!--  パスワードを忘れた場合-->
            <div class="mt-5">
                <a href="{{ route('password.request') }}" wire:navigate>
                    パスワードを忘れた場合
                </a>
            </div>

            <!-- ログイン状態の保持 -->
            <div class="mt-2">
                <label for="remember">
                    <input wire:model="form.remember" id="remember" type="checkbox">
                    <span>ログイン状態を保持する</span>
                </label>
            </div>

            <!-- ログインボタン -->
            <div class="mt-3">
                <x-submit-button>
                    ログイン
                </x-submit-button>
            </div>
        </div>
    </form>
</section>
