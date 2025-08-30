<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection; 
use App\Models\User;
use App\Models\Word;
use App\Models\Tag;
use Livewire\Attributes\On; 
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.words-app')] class extends Component 
{
    public string $word_name = '';
    public string $description = '';
    public array $checkedTagIds = [];
    public $checkedTags;

    public function mount()
    {
        //コレクション型として初期化　※プロパティ定義時コレクション型の定義ができないため。
        $this->checkedTags = collect();
    }

    //tags.check-listから引数を渡される
    #[On('show-checked-tags')]
    public function loadCheckTags($payload)
    {
        //受け取ったペイロードから、必要なデータを抽出
        $tagsArray = $payload['tags'];
        $this->checkedTagIds = $payload['checkedTagIds'];
        
        // 受け取った配列をLaravelのCollectionに変換する
        $tagsCollection = collect($tagsArray);
        
        $this->checkedTags = $tagsCollection->whereIn('id', $this->checkedTagIds); 
    }

    //フォーム送信時の処理
    public function wordCreate(): void
    {
        $validated = $this->validate([
            'word_name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        //Wordモデルに新しいレコードを挿入し、インスタンス化
        $word = Word::create([
            'word_name' => $this->word_name,
            'description' => $this->description,
            'user_id' => Auth::id(),
        ]);
        
        //attouch()によりtag_idとword_idが自動的に結び付けられ、中間テーブルに挿入される
        $word->tags()->attach($this->checkedTagIds);
        // tags() : Wordモデルのクラスメソッド。
        /*attach() : 
        中間テーブルへのレコード挿入: 中間テーブルに、関連付けに必要な外部キーのペア（例：word_idとtag_id）を自動的に挿入
        IDの自動取得: $word->tags()->attach(...)のように呼び出された際に、呼び出し元のモデル（この場合は $word）のIDを自動的に取得
        */

        $this->reset(['word_name', 'description']);

        $this->dispatch('wordsUpdated');
    }
}; ?>

<div class="container-fluid" x-data="{ showModal: false }" x-on:open-word-create-modal.window="showModal = true">

    <!-- モーダル本体 -->
    <div x-bind:class="{ 'modal': true, 'd-block': showModal }" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <form wire:submit.prevent="wordCreate" x-on:submit="showModal = false">
                    <!--「.prevent」：ブラウザのデフォルトのフォーム送信を無効化し、ページのリロードをなくす-->

                    <!--モーダルヘッダー-->
                    <div class="modal-header">
                        <h5 class="modal-title">新しい語句を追加</h5>

                        <!--閉じるボタン-->
                        <button type="button" class="btn-close"
                            x-on:click="showModal = false"aria-label="Close"></button>
                    </div>

                    <!-- モーダルボディ -->
                    <div class="modal-body">

                        <!--語句名フィールド-->
                        <div class="mb-3">
                            <label for="word_name" class="form-label">語句</label>
                            <input id="word_name" name="word_name" type="text" class="form-control"
                                wire:model="word_name" required>
                            
                        </div>

                        <!--語句説明フィールド-->
                        <div class="mb-3">
                            <label for="description" class="form-label">説明</label>
                            <textarea id="description" name="description" class="form-control" wire:model="description" rows="15" required></textarea>
                            
                        </div>

                        <!--タグ付け-->
                        <div>
                            <!-- タグチェックリストモーダル -->
                            <div>
                                <span x-on:click="$dispatch('open-tag-check-list')">
                                    タグ選択
                                </span>
                            </div>

                            <!-- 選択したタグを表示 -->
                            @if ($this->checkedTags)
                                <div class="d-flex">
                                    @foreach ($this->checkedTags as $checkedTag)
                                        <span class="me-2">
                                            {{ $checkedTag['tag_name'] }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!--保存ボタン-->
                    <div class="modal-footer d-flex justify-content-center">
                        <button type="submit" class="btn btn-primary">保存</button>
                    </div>
                </form>
            </div>
        </div>
        @livewire('pages.tags.check-list')
    </div>
</div>
