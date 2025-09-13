<?php

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Word;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On; // #[On('wordsUpdated')]の使用

new #[Layout('layouts.words-app')] class extends Component 
{
    public $wordColl;

    //語句リストの初期読み込み
    public function mount()
    {
        $this->loadWordColl();
    }

    // 語句リストの更新
    #[On('update-words')]
    public function loadWordColl()
    {
        $this->wordColl = Auth::user()->words()->orderBy('created_at', 'desc')->get();
    }
    
    //リスト内の語句をクリック時に実行
    public function showWordDetail(Word $word): void
    {
        // クリックした語句のモデルインスタンスを渡す
        $this->dispatch('openWordDetailModal', word: $word)
        ->to('pages.words.detail-and-edit');
    }
}; ?>

<!--語句リスト-->
<div>
    <ul class="list-group">
        @foreach ($this->wordColl as $word)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <!--語句のクリック時にshowWordDetailメソッドを実行し、詳細ページへ飛ぶ-->
                <a class="mb-0 text-dark text-decoration-none"  wire:click="showWordDetail({{ $word }})">
                    {{ $word->word_name }}
                </a> 
            </li>
        @endforeach
    </ul>
</div>
