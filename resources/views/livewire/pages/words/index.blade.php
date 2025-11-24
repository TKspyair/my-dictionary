<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout; //#[Layout('layouts.words-app')]の使用

new #[Layout('layouts.words-app')] class extends Component {};
?>

{{-- 
* container-md: スマホで全幅表示、タブレット以上で固定幅表示
--}}
<div class="container-md">

    <!-- ヘッダー部 -->
    <header class="d-flex align-items-center mx-2 my-4 p-0">

        <!-- メニューボタン -->
        <div class="justify-content-start p-0">
            <button data-bs-toggle="offcanvas" data-bs-target="#menu-index-offcanvas"
                class="btn btn-link text-dark p-0 border-0">
                <i class="bi bi-list fs-2 fw-bold"></i>
            </button>
        </div>
    
        <!--検索バー-->
        <div class="flex-grow-1 ms-3 me-4 p-0 ">
            @livewire('pages.words.search-word')
        </div>
    </header>

    <!-- ボディ部 -->
    <div>    
        <!--語句リスト-->
        <div class="m-4">
            @livewire('pages.words.words-list')
        </div>

        <!--語句登録ボタン-->
        <div>
            <button x-on:click="$dispatch('open-words-create-modal')" 
                class="position-fixed fab-button btn btn-primary rounded-circle shadow border-0" style="bottom: 40px; right: 25px;">
                <i class="bi bi-plus-lg"></i>
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
