<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout; //#[Layout('layouts.words-app')]の使用


new #[Layout('layouts.words-app')] class extends Component 
{
};
?>

<div class="container-fluid">
    <!--メニューボタン-->
    
    <!--オフキャンバス-->
    <div class="offcanvas offcanvas-start w-75" tabindex="-1" id="menu-offcanvas" aria-labelledby="offcanvas">
        <!-- ヘッダー -->
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvas">メニュー</h5>
        </div>
        
        <!-- ボディ -->
        <div class="offcanvas-body p-0 m-4">
            
            <!--タグメニュー-->
            <div class="d-flex justify-content-between align-text-center">
                <h6>タグ</h6>
                <span x-on:click="$dispatch('open-tag-modal')">設定</span>
            </div>
            
            <!--タグ一覧-->
            <div>
                @livewire('pages.tags.index')
            </div>
        </div>
    </div>
    
    <!-- タグ設定　-->
    @livewire('pages.tags.create')
</div>
