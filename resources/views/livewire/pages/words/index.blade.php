<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout; //#[Layout('layouts.words-app')]の使用

new #[Layout('layouts.words-app')] class extends Component {};
?>

<div class="container-lg">
    
    <div class="m-4 d-flex">
        <div class="justify-content-start">
            @livewire('pages.menu.index')
        </div>
    
        <div class="flex-grow-1 mx-3">
            @livewire('pages.words.search-word')
        </div>
    </div>

    <!--語句リスト-->
    <div class="m-4">
        @livewire('pages.words.words-list')
        @livewire('pages.words.show')
    </div>

    <!-- 語句登録モーダルOpenボタン-->
    <div>
        @livewire('pages.words.create')
    </div>
</div>
