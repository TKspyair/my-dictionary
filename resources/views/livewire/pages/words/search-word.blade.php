<?php

use App\Models\User;
use App\Models\Word;
use Illuminate\Database\Eloquent\Collection; //Collectionの定義
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed; //算出プロパティ

new #[Layout('layouts.words-app')] class extends Component 
{
    public string $search = ''; // 検索バーの入力値

    #[Computed]
    public function results(): Collection
    {
        if (strlen($this->search) === 0) {
            //空の配列を返す
            return new Collection();
        }
        return Word::where('word_name', 'like', '%' . $this->search . '%')
            ->limit(5)
            ->get();
    }

    //リスト内の語句をクリック時に実行
    public function showWordDetail(int $id): void
    {
        // 'showWordDetail'というイベントを発火させ、語句のIDを渡す(showコンポーネントへ)
        $this->dispatch('showWordDetail', $id);
    }
}; ?>

<div x-data="{ listOpen: false }" x-on:click.outside="listOpen = false" class="position-relative">
    <!--検索バー-->
    <input id="search_word" name="search_word" wire:model.live="search" x-on:focus="listOpen = true" type="text"
        class="form-control" placeholder="語句を検索..." autocomplete="off">
    <!--
    「wire: model="プロパティ"」 : フォームの入力値とLivewireコンポーネントのプロパティを同期させる。(この場合は$search)
    「.debounce」: Livewireがサーバーへリクエストを送信するのを一時的に遅延させるためのモディファイア
    「モディファイア（modifier）」: ディレクティブの動作をカスタマイズしたり、機能を拡張したりするために使用する特別なキーワード
    -->

    <!-- 検索結果の表示リスト -->
    @if ($this->results->count() > 0)
        <div x-show="listOpen" class="card position-absolute w-100 z-1">
            <ul class="list-group list-group-flush">
                @foreach ($this->results as $result)
                    <li class="list-group-item">
                        <a wire:click="showWordDetail({{ $result->id }})" class="text-dark text-decoration-none">{{ $result->word_name }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    <!--検索結果がないが、文字を入力しているときに実行-->
    @elseif($this->search)
        <div x-show="listOpen" class="card position-absolute w-100 z-1">
            <div class="card-body">
                <p class="mb-0 text-muted">語句が見つかりません。</p>
            </div>
        </div>
    @endif
</div>
