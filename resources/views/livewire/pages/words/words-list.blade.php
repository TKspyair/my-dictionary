<?php

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Word;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On; // #[On('wordsUpdated')]の使用

new #[Layout('layouts.words-app')] class extends Component 
{
    public $words;

    //語句リストの初期読み込み
    public function mount()
    {
        $this->loadWords();
    }

    // 語句リストの更新
    #[On('wordsUpdated')]
    public function loadWords()
    {
        $this->words = Auth::user()->words()->orderBy('created_at', 'desc')->get();
    }
    
    //リスト内の語句をクリック時に実行
    public function showWordDetail(int $id): void
    {
        // 'showWordDetail'というイベントを発火させ、語句のIDを渡す(showコンポーネントへ)
        $this->dispatch('showWordDetail', $id);
    }
}; ?>

<!--語句リスト-->
<div>
    <ul class="list-group">
        @foreach ($this->words as $word)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <!--語句のクリック時にshowWordDetailメソッドを実行し、詳細ページへ飛ぶ-->
                <a class="mb-0 text-dark text-decoration-none"  wire:click="showWordDetail({{ $word->id }})">{{ $word->word_name }}</a> 
            </li>
        @endforeach
    </ul>
</div>
