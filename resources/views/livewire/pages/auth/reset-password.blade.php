<?php
//TODO: Bladeコンポーネントのtext-inputとinput-errorをform-inputと統合する

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.words-app')] class extends Component 
{
//======================================================================
// プロパティ
//======================================================================
    /**
     * #[Locked]: ブラウザからの変更を無視(サーバーサイドからのみ変更可能)
     * > URLや隠しフィールドから読み込まれ、変更されてはならない重要なデータを保護するために使用する
    */

    #[Locked]
    public string $token = '';

    public string $email = '';
    public string $password = '';
    # パスワード確認用
    public string $password_confirmation = '';

    //-----------------------------------------------------
    // 初期化
    //-----------------------------------------------------
    public function mount(string $token): void
    {
        $this->token = $token;

        /**
         * request(): リクエストクラスのインスタンスを返す
         * string('キー名'): キーに対応する入力値を文字列型にして取得
        */
        # pages/auth/forgot-passwordで入力されたメールアドレスを取得
        $this->email = request()->string('email');
    }

    # ユーザーのパスワードをリセット
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'], 
            'email' => ['required', 'string', 'email'], 
            /** Rules\Password::defaults(): Laravelのデフォルトのパスワード規則に従う 
             * vendor/laravel/framework/src/Illuminate/Validation/Rules/Password.php
            */
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        /**  Password::reset('email', 'password', 'password_confirmation', 'token',[コールバック関数])
         * > 第1引数(認証情報と検証データ)の検証成功時、第2引数のコールバック関数が実行される
         * @戻り値 成功時: 'password.reset'  失敗時: 'password.token','password.user'
         * only(): 配列や連想配列から指定したキーやプロパティの値のみを抽出する 
         * forceFill(): $fillable(一括代入の制限)を無視して、複数の属性を更新する
        */
        # 検証結果の取得とパスワードとトークンのDBへの登録
        $status = Password::reset($this->only('email', 'password', 'password_confirmation', 'token'), function ($user) :string {
            $user->forceFill([
                    # 新しいパスワードをハッシュ化(暗号化)する
                    'password' => Hash::make($this->password),
                    # 新しいログイン状態を保持するトークンを代入する(長さ60文字のランダムな文字列のトークンを生成)
                    'remember_token' => Str::random(60), 
                #上記2つをDBに登録
                ])->save(); 

            # パスワードリセットイベントを発火
            event(new PasswordReset($user));
        });

        /** 
         * Password::PASSWORD_RESET: 検証成功時に取得する文字列'password.reset'(値)の公開定数(キー)
         * ※公開定数: public(アクセス修飾子)を宣言している定数、定義場所の外部での使用を前提としている
         * addError(string [フォームフィールド], string [エラーメッセージ])
         * > エラーメッセージをフォームフィールドに関連付けて表示する
         * __('文字列'): 文字列を現在のアプリの設定言語に翻訳し取得する
        */
        # 検証失敗時、メールアドレスフィールドにエラーメッセージを関連付けて表示する　※バリデーションエラーとは別のため注意
        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));
            
            return;
        }


        /**
         * Session::flash('キー', '値'): データをセッションに保存し、次のHTTPリクエストが処理された後、自動で削除される
         * ※session()(ヘルパー関数): データをセッションに保存し、ユーザーがログアウトするか、セッションの有効期限が切れるまで保存
        */
        # パスワードリセット成功メッセージをセッションに一時保存(一度だけ表示)
        Session::flash('status', __($status));

        #? ログインページにリダイレクトし、Livewireのソフトナビゲーションを有効化
        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div class="conteiner-md">
    <div class="d-flex flex-column justify-content-center align-items-center vh-100">
        <!-- アプリアイコンの設定(仮) -->
        <div>
            <i class="bi bi-book fs-1"></i>
        </div>

        <form wire:submit="resetPassword">
            <!-- メールアドレス -->
            <div class="mt-4">
                <x-text-input type="email" wire:model="email"
                    autofocus autocomplete="username" placeholder="メールアドレス" />
                <x-input-error :messages="$errors->get('email')"/>
            </div>

            <!-- パスワード -->
            <div class="mt-4">
                <x-text-input type="password" wire:model="password" 
                    autocomplete="new-password" placeholder="パスワード"/>
                <x-input-error :messages="$errors->get('password')" />
            </div>

            <!-- パスワード確認 -->
            <div class="mt-4">
                <x-text-input type="password"  wire:model="password_confirmation"
                    autocomplete="new-password" placeholder="パスワード確認"/>

                <x-input-error :messages="$errors->get('password_confirmation')"/>
            </div>

            <!-- パスワードリセット確定ボタン -->
            <div class="d-flex justify-content-center mt-4">
                <x-submit-button>
                    {{ __('Reset Password') }} 
                </x-submit-button>
            </div>
        </form>
    </div>
</div>