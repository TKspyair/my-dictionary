<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout; //#[Layout('layouts.words-app')]の使用


new #[Layout('layouts.words-app')] class extends Component 
{
};
?>

<div class="container-fluid">
    <!--メニューボタン-->
    <button type="button" class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvas">
        <i class="bi bi-list"></i>
    </button>
    <!--オフキャンバス-->
    <div class="offcanvas offcanvas-start w-75" tabindex="-1" id="offcanvas" aria-labelledby="offcanvas">
        <!-- ヘッダー -->
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvas">メニュー</h5>
        </div>
        
        <!-- ボディ -->
        <div class="offcanvas-body p-0">
            <div class="d-flex justify-content-between align-text-center">
                <span>タグ</span>
                @livewire('pages.tags.index')
            </div>
            @livewire('pages.menu.tag-list')
        </div>
    </div>
</div>
