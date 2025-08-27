<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout; //#[Layout('layouts.words-app')]の使用

new #[Layout('layouts.words-app')] class extends Component {};
?>

<div class="container-lg">
    
    <!-- ヘッダー -->
    <div class="m-4 d-flex">

        <!-- メニューボタン -->
        <div class="justify-content-start">
            <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#menu-offcanvas"> <!-- data-bs-targetで動作させたい要素のidを指定-->
                <i class="bi bi-list"></i>
            </button>
        </div>
    
        <!--検索バー-->
        <div class="flex-grow-1 mx-3">
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
            <button x-on:click="$dispatch('open-word-create-modal')" class="fab-button btn btn-primary rounded-circle shadow-lg position-fixed bottom-0 end-0 m-4">
                <i class="fas fa-plus"></i><!--「＋」マークの表示-->
            </button>
        </div>
    </div>

    <!--以下はレイアウト関係ないコンポーネント-->
    
    <!-- 語句登録モーダル-->
    @livewire('pages.words.create')
    <!-- メニュー -->
    @livewire('pages.menu.index')
    <!-- 語句詳細 -->
    @livewire('pages.words.show')
</div>
