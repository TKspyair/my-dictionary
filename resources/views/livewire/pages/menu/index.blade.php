<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Livewire\Actions\Logout; 

new #[Layout('layouts.words-app')] class extends Component 
{
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
};
?>

<div class="container-fluid">

    <!--オフキャンバス-->
    <div class="offcanvas offcanvas-start w-75" tabindex="-1" id="menu-index-offcanvas">
        {{-- words/indexと連携し、offcanvasを開く
    - オフキャンバスを開く
    > data-bs-toggle="offcanvas" data-bs-target="#[指定のid]
    >> data-bs-toggleで操作するBootstrapクラスを指定、data-bs-targetでidを指定することで狙った要素を操作する
    --}}
        <!-- ヘッダー -->
        <div class="offcanvas-header">
            <span class="offcanvas-title fs-6 fw-bold" id="offcanvas">メニュー</span>
        </div>

        <!-- ボディ -->
        <div class="offcanvas-body">

            <!--タグメニュー-->
            <div class="d-flex justify-content-between align-text-center m-1">
                <h6>タグ</h6>
                {{-- $dispatchでtags.createのモーダルを開く --}}
                <span x-on:click="$dispatch('open-tags-create-modal')" data-bs-dismiss="offcanvas">設定</span>
            </div>

            <!--タグ一覧-->
            <div class="m-0 p-0">
                @livewire('pages.tags.index')
            </div>

            <!-- ログアウトボタン -->
            <div>
                <button type="button" wire:click="logout">
                    ログアウト
                </button>
            </div>
        </div>
    </div>

    <!-- タグ設定　-->
    @livewire('pages.tags.create')
</div>
