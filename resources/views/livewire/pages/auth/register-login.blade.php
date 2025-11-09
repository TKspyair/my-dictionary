<?php
//TODO: 
//* registerとloginをタブ切り替えで統合
//* ルーティング機能を調べる

use Livewire\Volt\Component;
use Livewire\Attributes\Layout; 

new #[Layout('layouts.words-app')] class extends Component {};
?>

{{-- 
* container-md: スマホで全幅表示、タブレット以上で固定幅表示
--}}
<div class="container-md" x-data="{ showMoadal: true }">
    <div class="d-flex justify-content-around">
        <!-- 新規登録タブ -->
        <button type="button" x-on:click="showModal = !showModal">
            新規登録
        </button>
        <!-- ログインタブ -->
        <button type="button" x-on:click="showModal = !showModal">
            ログイン
        </button>
    </div>

    <!-- アカウント作成モーダル -->
    <div x-show="showModal">
        <div class="modal d-block" tabindex="-1">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <header class="modal-header p-2">
                        <span>a</span>
                    </header>
                    <div class="modal-body">
                    </div>
                </div>
            </div>
        </div>
    </div>
    @livewire('')

    <!-- ログイン -->
    <div x-show="!showModal">
        <div class="modal d-block" tabindex="-1">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <header class="modal-header p-2">
                        <span>b</span>
                    </header>
                    <div class="modal-body">
                    </div>
                </div>
            </div>
        </div>
    </div>
    @livewire('')
</div>
