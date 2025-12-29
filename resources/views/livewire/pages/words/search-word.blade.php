<?php

use App\Models\User;
use App\Models\Word;
use Illuminate\Database\Eloquent\Collection; //Collectionの定義
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed; //算出プロパティ

new #[Layout('layouts.words-app')] class extends Component 
{
    // 検索バーの入力値
    public string $search = ''; 

    //検索した語句のコレクション
    #[Computed]
    public function words(): Collection
    {
        //プロパティの初期化
        if (!$this->search) {
            //空の配列を返す
            return new Collection();
        }
        return Word::where('word_name', 'like', '%' . $this->search . '%')
            ->limit(5)
            ->get();
    }

    //リスト内の語句をクリック時に実行
    public function sendWordId(int $wordId): void
    {
        $this->dispatch('send-word-id', wordId: $wordId)
        ->to('pages.words.detail');

        $this->reset('search');
    }
}; ?>

<section x-data="{ showWordList: false }" x-on:click.outside="showWordList = false" class="position-relative">
    <!-- 検索バー -->
    {{-- 
    NOTE:
    * form-controlはresouces/css/custom.cssでカスタマイズしているため使用に注意
    --}}
    <div class="input-group border rounded-2">
        <input  type="text" wire:model.live="search" x-on:focus="showWordList = true" 
            class="form-control py-1 fs-6" autocomplete="off">

        <span class="input-group-text bg-transparent border-0">
            <!-- 虫眼鏡マーク -->
            <i class="bi bi-search"></i>
        </span>
    </div>

    <!-- 検索結果の表示リスト -->
    <article x-show="showWordList">
        
        @if ($this->search && $this->words->count() > 0)
            <div class="mt-1 card position-absolute w-100 z-1">
                <ul class="list-group list-group-flush">
                    @foreach ($this->words as $word)
                        <li wire:key="{{ $word->id }}" class="list-group-item">
                            <a class="text-dark text-decoration-none"
                                wire:click="sendWordId({{ $word->id }})"
                                x-on:click="$dispatch('open-words-detail-modal')">
                                {{ $word->word_name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        <!--検索結果がないが、文字を入力しているときに実行-->
        @elseif($this->search && $this->words->count() === 0)
            <div class="mt-1 card position-absolute w-100 z-1">
                <div class="card-body">
                    <p class="mb-0 text-muted">語句が見つかりません。</p>
                </div>
            </div>
        
        @endif
    </article>
</section>
