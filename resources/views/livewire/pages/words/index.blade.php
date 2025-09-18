<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout; //#[Layout('layouts.words-app')]の使用

new #[Layout('layouts.words-app')] class extends Component {};
?>

<div class="container-lg">
    
    <!-- ヘッダー -->
    <div class="d-flex mx-2 my-4 p-0">

        <!-- メニューボタン -->
        <div class="justify-content-start p-0">
            <button data-bs-toggle="offcanvas" data-bs-target="#menu-index-offcanvas"
                class="btn btn-link text-dark p-0 border-0"> <!-- data-bs-targetで動作させたい要素のidを指定-->
                <i class="bi bi-list fs-2"></i>
            </button>
        </div>
    
        <!--検索バー-->
        <div class="flex-grow-1 ms-3 me-4 p-0 ">
            @livewire('pages.words.search-word')
        </div>
    </div>

    <!-- ボディ -->
    <div>    
        <!--語句リスト-->
        <div class="m-4">
            @livewire('pages.words.words-list')
        </div>

        <!--語句登録ボタン-->
        <div>
            <button x-on:click="$dispatch('open-words-create-modal')" 
                class="fab-button btn btn-primary rounded-circle shadow-lg position-fixed bottom-0 end-0 m-4">
                <i class="fas fa-plus"></i><!--「＋」マークの表示-->
            </button>
        </div>
    </div>

    <!--以下はレイアウト関係ないコンポーネント-->
    
    <!-- 語句登録モーダル-->
    @livewire('pages.words.create')
    <!-- メニュー -->
    @livewire('pages.menu.index')
    <!-- 語句詳細・編集 -->
    @livewire('pages.words.detail-edit')
    <!-- テスト時のみ有効化 -->
    <!--@ livewire('pages.test')-->
</div>
