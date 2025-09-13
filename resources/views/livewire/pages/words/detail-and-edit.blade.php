<?php
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Word;
use App\Models\Tag;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On; // #[On('wordsUpdated')]の使用
use Livewire\Volt\Component;

new #[Layout('layouts.words-app')] class extends Component 
{
    //選択した語句のインスタンスを格納
    public $word = null;

    //word_nameの値
    public string $wordName = '';

    //descriptionの値
    public string $wordDescription = '';

    //$wordに紐づくtagsコレクション
    public $checkedWordTags = null;

    public array $checkedWordTagIds = [];

    //語句詳細モーダルを開く
    #[On('open-word-detail-Modal')]
    public function openWordDetailModal(Word $word): void
    {
        $this->word = $word;
        $this->wordName = $this->word->word_name;
        $this->wordDescription = $this->word->description;
        $this->checkedWordTags = $this->word->tags;

        // Alpine.jsにモーダルを開くイベントとデータを送信
        $this->dispatch('open-word-detail-modal');
    }

    //チェックしたタグのidを配列で渡す　※コレクション型は$dispatchで送れないため
    public function checkedWordTagIds()
    {
        $this->checkedWordTagIds = $this->checkedWordTags->pluck('id')->all();

        dd($this->checkedWordTagIds);
        
        $this->dispatch('edit-checked-word-tag-ids', checkedWordTagIds: $this->checkedWordtagIds)->to('pages.tags.check-list');
    }

    //tags.check-listからチェックしたタグのidを配列で受け取る
    #[On('return-checked-tag-ids')]
    public function loadCheckedTags(array $checkedTagIds)
    {
        $this->checkedWordTags = null;
        //チェックしたタグのidをもとに、$checkedWordTagsの値を更新
        $this->checkedWordTags = Tag::whereIn('id', $checkedTagIds)->get();
    }

    //語句の更新
    public function wordUpdate(): void
    {
        //新しい入力値をwordsテーブルに挿入
        $this->word->update([
            'word_name' => $this->wordName,
            'description' => $this->wordDescription,
        ]);

        // タグのリレーションを更新
        $this->word->tags()->sync($this->checkedWordTags->pluck('id')->all());
        /*sync(array id) : 引数で渡されたidの配列とword_tag(中間テーブル)のid同期する
            > word_tag:[1,3] sync:[1,5] >> 更新された結果:[1,5]
        */
        
        // wordsテーブルの更新イベントを渡す(words/indexへ)
        $this->dispatch('update-words');
    }

    //語句の削除
    public function wordDelete(): void
    {
        // 現在編集中の語句をDBから削除
        $this->word->delete();

        // wordsテーブルの更新イベントを渡す
        $this->dispatch('update-words');
    }
};
?>


<div class="container-lg" x-data="{ showModal: false, EditMode: false }" x-on:open-word-detail-modal="showModal = true"
    x-on:close-all-modal.window="showModal = false">

    <!-- モーダル本体 -->
    <div x-bind:class="{ 'modal': true, 'd-block': showModal }" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">

                @if ($word)
                    <!-- ボディ -->
                    <div class="modal-body">

                        <!-- 詳細モード -->
                        <div x-show="!EditMode">

                            <!-- 画面上部操作アイコン群 -->
                            <div class="d-flex justify-content-between align-items-center my-2">

                                <!--戻るボタン-->
                                <span x-on:click="showModal = false" data-bs-dismiss="modal" class="p-0 me-3">
                                    <i class="bi bi-arrow-left fs-4"></i>
                                </span>
                            

                                <!-- その他 -->
                                <div class="dropdown">
                                    <span data-bs-toggle="dropdown" class="me-2">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </span>
                                    <ul class="dropdown-menu p-1">

                                        <!-- 編集ボタン クリックで編集モードON-->
                                        <li x-on:click="EditMode =true" class="m-1">
                                            <i class="bi bi-pencil me-1"></i>
                                            <span>編集</span>
                                        </li>

                                        <!-- 削除ボタン -->
                                        <li wire:click="wordDelete" wire:confirm="本当に削除しますか？"
                                            x-on:click="showModal = false" class="m-1">
                                            <i class="bi bi-trash me-1"></i>
                                            <span>削除</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- 語句名 -->
                            <div class="mt-4">
                                <h5>{{ $this->wordName }}</h5>
                            </div>

                            <hr class="m-0 p-0">

                            <!-- 説明フィールド -->
                            <div>
                                <p class="text-break">
                                    {{ $this->wordDescription }}
                                </p>
                            </div>

                            <!-- タグ一覧 -->
                            <div class="d-flex">
                                @foreach ($this->checkedWordTags as $checkedWordTag)
                                    <span class="badge bg-secondary me-1">
                                        {{ $checkedWordTag->tag_name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        <!-- ここまで詳細 -->

                        <!-- 編集モーダル -->
                        <div x-show="EditMode">
                            <form wire:submit.prevent="wordUpdate">

                                <!-- 画面上部 -->
                                <div>
                                    <!--戻るボタン-->
                                    <div class="d-flex align-items-center my-2">
                                        <span x-on:click="EditMode = false" class="me-3">
                                            <i class="bi bi-arrow-left fs-4"></i>
                                        </span>
                                        
                                        <h5 class="m-0">編集</h5>
                                    </div>
                                </div>
                                
                                <!-- 語句フィールド $wordName -->
                                <div class="mt-4">
                                    <input type="text" class="form-control border-0 fs-5 fw-bold"
                                        wire:model="wordName" required>
                                </div>

                                <hr class="m-0 p-0">

                                <!-- 説明フィールド $wordDescription-->
                                <div>
                                    <textarea wire:model="wordDescription" class="form-control border-0" rows="15" required></textarea>
                                </div>

                                <!-- タグ選択コンポーネントを開く -->
                                <span x-on:click="$dispatch('open-tags-check-list')" wire:click="checkedWordTagIds">
                                    タグ選択
                                </span>
                                <!-- チェックしたタグ一覧 -->
                                <div class="d-flex">
                                    @foreach ($this->checkedWordTags as $checkedWordTag)
                                        <span class="badge bg-secondary me-1">
                                            {{ $checkedWordTag->tag_name }}
                                        </span>
                                    @endforeach
                                </div>

                                <hr class="p-0 m-0">

                                <!-- 画面下部ボタン群 -->
                                <div class="d-flex justify-content-between ">

                                    <!-- 削除ボタン -->
                                    <button x-on:click="$dispatch('close-all-modal')" wire:click="wordDelete"
                                        wire:confirm="本当にこの投稿を削除しますか？" class="btn btn-danger">削除</button>

                                    <!-- 更新ボタン -->
                                    <button type="submit" x-on:click="EditMode = false" class="btn btn-primary">更新</button>
                                </div>
                            </form>
                            <!-- タグチェックリスト -->
                            @livewire('pages.tags.check-list')
                        </div>
                        <!-- ここまで編集モーダル -->
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
