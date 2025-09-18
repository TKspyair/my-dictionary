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
    public function wordColl(): Collection
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
    public function showWordDetail(Word $word): void
    {
        $this->dispatch('open-words-detail-and-edit-modal', word: $word)
        ->to('pages.words.detail');
    }
}; ?>

<section x-data="{ showWordList: false }" x-on:click.outside="showWordList = false" class="position-relative">
    <!-- 検索バー -->
    <input  type="text" wire:model.live="search" x-on:focus="showWordList = true" 
        class="form-control" autocomplete="off"  placeholder="語句を検索...">

    <!-- 検索結果の表示リスト -->
    <article x-show="showWordList">
        @if ($this->search && $this->words->count() > 0)
            <div class="card position-absolute w-100 z-1">
                <ul class="list-group list-group-flush">

                    @foreach ($this->wordColl as $word)
                        <li wire:key="{{ $word->id }}" class="list-group-item">
                            <a wire:click="showWordDetail({{ $word }})" class="text-dark text-decoration-none">
                                {{ $word->word_name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        <!--検索結果がないが、文字を入力しているときに実行-->
        @elseif($this->search && $this->wordColl->count() === 0)
            <div class="card position-absolute w-100 z-1">
                <div class="card-body">
                    <p class="mb-0 text-muted">語句が見つかりません。</p>
                </div>
            </div>
        @endif
    </article>
</section>
