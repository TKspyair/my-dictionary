<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout; //#[Layout('layouts.words-app')]の使用

new #[Layout('layouts.words-app')] class extends Component {};
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
        </div>
    </div>

    <!-- タグ設定　-->
    @livewire('pages.tags.create')
</div>
