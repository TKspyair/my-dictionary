<?php
/** verify-email.blade.php: ユーザーのメールアドレスが有効か確認する処理を扱う
 * 
 * 
 * 
*/
use App\Livewire\Actions\Logout;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.words-app')] class extends Component
{
    /** sendVerifycation()
     * hasVerifycation(): メール認証が完了しているかのチェック
     * redirectIntended(): インテンデッドURLにリダイレクトする(デフォルト値はインテンデッドURLが存在しないときのもの) ※intended: 意図された
     * > つまり、メール認証済みならこのファイルの処理は終了する
     * 
     * ※インテンデッドURL: セッションに保存される、未認証ユーザーが本来アクセスしようとしていたURL
     * > ユーザー認証が必要なページに未認証状態でアクセスしようとした場合、
     *   Laravelのauthミドルウェアはそのリクエストをとらえ、ユーザーを(pages.auth.register-login)にリダイレクトし、認証を促す
     *   その際にインテンデッドURLがセッションに保存される
     *    
     * 　
    */
    public function sendVerification(): void
    {
        # メール認証成功時、pages.words.indexにリダイレクトし、このファイルの処理を終了する
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: RouteServiceProvider::HOME, navigate: true);

            return;
        }

        # メール認証失敗時、ユーザーにメールアドレス認証のための通知メールを送信する
        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    # ログアウト処理
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <div class="">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600"> 
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <x-submit-button wire:click="sendVerification">
            {{ __('Resend Verification Email') }}
        </x-submit-button>

        <button wire:click="logout" type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            {{ __('Log Out') }}
        </button>
    </div>
</div>
