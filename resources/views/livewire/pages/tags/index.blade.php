<?php

//TODO: クリックしたタグをもつ語句だけを表示するページは現在のところ不要かもしれないので、そこにつながるメソッドのみコメントアウトしている
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
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
    #[On('update-tag-list')]
    public function loadTags(): void
    {
        $this->tags = Auth::user()->tags()->orderBy('created_at', 'desc')->get();
    }

    # クリックされたタグをもつ語句だけを表示するために、選択されたタグのidを渡す
    public function sendSelectedTag($tag)
    {
        $this->dispatch('send-selected-tag-instance',tag: $tag)
            ->to('pages.words.tag-words-show');
    } 
};
?>

<!--タグ一覧-->
<div>
    <ul class="list-group list-group-flush m-0 p-0">
        @foreach ($this->tags as $tag)
            {{--
            * クリックされたタグをもつ語句のみを表示するページを開くコード 
            ** 専用のモーダルを開く
            * x-on:click="$dispatch('open-tag-words-show-modal')"
            ** サーバーサイドでのタグのidを渡すメソッドを起動
            * wire:click="sendSelectedTag({{ $tag }})" 
            * ※上記の2つを併用しないとエラーが起きるので注意
            --}}
            <li 
                wire:key="{{ $tag->id }}" 
                class="list-group-item d-flex align-items-center p-0 ms-1 my-2 border-0">
                <i class="bi bi-tag me-2"></i>
                <span class="mb-0 text-dark text-decoration-none">{{ $tag->tag_name }}</span>
            </li>
        @endforeach
    </ul>
    @livewire('pages.words.tag-words-show')
</div>
