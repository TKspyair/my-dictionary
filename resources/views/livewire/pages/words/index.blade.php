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
            @livewire('pages.words.search-word', key('pages.words.search-word'))
        </div>
    </header>

    <!-- ボディ部 -->
    <div>    
        <!--語句リスト-->
        <div class="m-4">
            @livewire('pages.words.words-list', key('pages.words.words-list'))
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
    {{-- NOTE: メインビューにコンポーネント定義を集約する理由 
    ** 1. 各コンポーネントを親子関係ではなく、兄弟関係にするため
    * > 親子関係にしてしまうと、子コンポーネントに意図しないレンダリングが発生してエラーの原因になる
    * >> コンポーネントIDのリセットによる、「Component Not Found」など ※LivewireではランダムなIDによりコンポーネントを識別する
    * >>> もし親子関係にする場合、wire:ignoreにより意図しないレンダリングで、エラーを防ぐ
    * 例　<div wire:ignore>コンポーネント定義</div> 
    --}}
    
    <!-- 語句登録モーダル-->
    @livewire('pages.words.create', key('pages.words.create'))
    <!-- メニュー -->
    @livewire('pages.menu.index', key('pages.menu.index'))
    <!-- 語句詳細画面 -->
    @livewire('pages.words.detail', key('pages.words.detail'))
    <!-- 語句編集画面 -->
    @livewire('pages.words.edit', key('pages.words.edit'))
    <!-- タグ選択画面 -->
    @livewire('pages.tags.select-tag', key('pages.tags.select-tag'))
</div>
