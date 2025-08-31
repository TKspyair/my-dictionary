<?php
use Livewire\Volt\Component;
use Livewire\Attributes\Layout; //#[Layout('layouts.words-app')]の使用
use Livewire\Attributes\On;

new #[Layout('layouts.words-app')] class extends Component
{
    public $tags;

    //初期読み込み時に実行
    public function mount()
    {
        $this->loadTags();
    }

    //　タグ一覧の更新
    #[On('tagListUpdate')]
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
            <li class="list-group-item d-flex align-items-center p-2">
                <i class="bi bi-tag me-2"></i>
                <a class="mb-0 text-dark text-decoration-none">{{ $tag->tag_name }}</a>
            </li>
        @endforeach
    </ul>
</div>
