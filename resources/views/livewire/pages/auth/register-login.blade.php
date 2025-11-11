<?php
//TODO:
//* registerとloginをタブ切り替えで統合
//* ルーティング機能を調べる

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;

new #[Layout('layouts.words-app')] class extends Component 
{
    public function clearForm()
    {
        $this->dispatch('clear-form-register')->to('pages.auth.register');
    }
};
?>

{{-- 
* container-md: スマホで全幅表示、タブレット以上で固定幅表示
* x-data="{ isAuthenticated: true }": 「認証済みかどうか」という意味のプロパティだが、現在の機能ではサーバーサイドとは関係はない
--}}
<div class="container-md" x-data="{ activeTab: 'login' }">

    <!-- ログインモーダル -->
    <div>
        <div class="modal d-block" tabindex="-1">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content vh-100"> {{-- モーダル画面の親要素 --}}

                    <!-- ヘッダー部 -->
                    <header class="modal-header p-0">
                        <div class="d-flex w-100 btn-group">
                            <!-- 新規登録ページボタン -->
                            <button type="button" class="w-50 btn btn-outline-primary"
                                x-bind:class="{ 'text-white bg-primary': activeTab === 'register' }"
                                x-on:click="activeTab = 'register'"
                                wire:click="clearForm">
                                新規登録
                            </button>
                            <!-- ログインページボタン -->
                            <button type="button" class="w-50 btn btn-outline-primary"
                                x-bind:class="{ 'text-white bg-primary': activeTab === 'login' }"
                                x-on:click="activeTab = 'login'"
                                wire:click="clearForm">
                                ログイン
                            </button>
                        </div>
                    </header>

                    <!-- ボディ部 -->
                    <div class="modal-body">
                        <!-- ログインページ -->
                        <div class="h-100" x-show="activeTab === 'login'">
                            @livewire('pages.auth.login')
                        </div>

                        <!-- 新規登録ページ -->
                        <div class="h-100" x-show="activeTab === 'register'">
                            @livewire('pages.auth.register')
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
