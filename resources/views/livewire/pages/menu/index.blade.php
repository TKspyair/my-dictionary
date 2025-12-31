<?php
//TODO: ログアウトボタンの配置
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Livewire\Actions\Logout;

new #[Layout('layouts.words-app')] class extends Component 
{
    //======================================================================
    // プロパティ
    //======================================================================

    public string $password = '';

    //======================================================================
    // メソッド
    //======================================================================
    # ログアウトを実行する　my-dictionary\app\Livewire\Actions\Logout.php
    /** 依存性の注入(Dependency(依存性) Injyection(注入))
     * あるクラスが必要とする依存オブジェクトを、クラスの外部から提供(注入)する手法
     * ※依存性の注入を使用しない場合: new [クラス名()]で子クラスを作成し、メソッドを実行する
     *
     **メソッドインジェクション(LaravelでのDI)
     * 1 メソッドの引数に型ヒントとしてクラス名(例: Logout)を指定する
     * 2 サービスコンテナが引数で指定されたクラスのインスタンスを引数(例: $logout)に代入する
     * ※　$logout = new App\Livewire\Actions\Logout();のような状態になる
     * 3 引数をメソッドとして実行すると(例: $logout())、元のクラスの__invoke()が実行される
     * ※invoke(): クラスが関数のように振舞えるようにするマジックメソッド
     *
     */
    public function logout(Logout $logout): void
    {
        # 依存性の注入 で取得したLogoutアクションを実行
        $logout();

        // ログアウト後のリダイレクト
        $this->redirect('/', navigate: true);
    }

    # ユーザーアカウント削除
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        /** tap(Auth::user(), $logout(...))->delete();
         * 1 フロント側のデータを削除: ログアウト処理をして、セッションと認証トークンを無効化
         * 2 サーバー側のデータを削除: ユーザー情報をDBから削除
         * ※ログアウト処理をしないと、ユーザー情報削除後もログイン後のページにアクセスできてしまう可能性があるため
         *
         * **tap(第1引数, [コールバック関数])
         * 1 第1引数を第2引数のコールバック関数に渡す
         * 2 コールバック関数実行
         * 3 コールバック関数の結果を第1引数に代入する
         *
         ** $logout(...)の「...」: コールバック関数のショートハンド記法
         * 通常の記法: tap(Auth::user(), function ($user) use ($logout) { $logout($user); })
         * 今回の記法: tap(Auth::user(), $logout(...))
         * ※三項演算子も単純なif文のショートハンド記法
         */
        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
};
?>

<div class="container-md">

    <!--オフキャンバス-->
    <div class="offcanvas offcanvas-start w-75" tabindex="-1" id="menu-index-offcanvas">
        {{-- words/indexと連携し、offcanvasを開く
    - オフキャンバスを開く
    > data-bs-toggle="offcanvas" data-bs-target="#[指定のid]
    >> data-bs-toggleで操作するBootstrapクラスを指定、data-bs-targetでidを指定することで狙った要素を操作する
    --}}
        <!-- ヘッダー -->
        <div class="offcanvas-header">
            <span class="offcanvas-title fs-5 fw-bold" id="offcanvas">メニュー</span>
        </div>

        <!-- ボディ -->
        <div class="offcanvas-body bg-white">

            <hr>
            <!--タグメニュー-->
            <div class="d-flex justify-content-between align-text-center m-1">
                <span class="fs-6 fw-bold">タグ</span>
                <span class="fs-6" x-on:click="$dispatch('open-tags-create-modal')"
                    data-bs-dismiss="offcanvas">編集</span>
            </div>

            <!--タグ一覧-->
            <div class="m-0 p-0">
                @livewire('pages.tags.index', key('pages.tags.index'))
            </div>
            <hr>

            <div class="dropdown dropend m-1">
                <span class="dropdown-toggle fs-6 fw-bold" data-bs-toggle="dropdown">
                    アカウント設定
                </span>
                <ul class="dropdown-menu">
                    <!-- ログアウトボタン -->
                    <li class="mb-1">
                        <span class="text-dark fs-6 ps-3" wire:click="logout" wire:confirm="ログアウトしますか？">
                            ログアウト
                        </span>
                    </li>
                    <!-- アカウント削除ボタン -->
                    <li class="mb-1">
                        <span class="btn text-danger fs-6 ps-3" wire:click="deleteUser" wire:confirm="本当にアカウントを削除しますか？">
                            アカウント削除
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- タグ設定　-->
    @livewire('pages.tags.create', key('pages.tags.create'))
</div>
