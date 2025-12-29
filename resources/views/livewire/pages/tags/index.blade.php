<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;

new #[Layout('layouts.words-app')] class extends Component
{
    //======================================================================
    // プロパティ
    //======================================================================
    public $tags;

    //======================================================================
    // 初期化
    //======================================================================
    //初期読み込み時に実行
    public function mount()
    {
        $this->loadTags();
    }

    //======================================================================
    // メソッド
    //======================================================================
    # タグ一覧の更新
    #[On('update-tag-list')]
    public function loadTags(): void
    {
        $this->tags = Auth::user()->tags()->orderBy('created_at', 'desc')->get();
    }
};
?>

<!--タグ一覧-->
<div>
    <ul class="list-group list-group-flush m-0 p-0">
        @foreach ($this->tags as $tag)
            <li 
                wire:key="{{ $tag->id }}" 
                class="list-group-item d-flex align-items-center p-0 ms-1 my-2 border-0">
                <i class="bi bi-tag me-2"></i>
                <span class="mb-0 text-dark text-decoration-none">
                    {{ $tag->tag_name }}
                </span>
            </li>
        @endforeach
    </ul>
</div>
