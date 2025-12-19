<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Word;
use App\Models\Tag;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;

use Livewire\Volt\Component;

new #[Layout('layouts.words-app')] class extends Component {
    //======================================================================
    // プロパティ
    //======================================================================
    public $word;

    public string $wordName = '';

    public string $wordDescription = '';

    # タグ選択モーダルでタグ一覧の表示に使用する
    public $tags;

    public array $selectedTagIds = [];

    # 選択したタグのコレクション
    #[Computed]
    public function selectedTags()
    {
        /** 引数の値が空の場合の処理がない理由
         * whereInは空の値を渡されたときに、空のコレクションを返すため */
        return Tag::whereIn('id', $this->selectedTagIds)->get();
    }

    //======================================================================
    // 初期化
    //======================================================================
    public function mount()
    {
        $this->loadTags();
    }

    // タグ一覧の更新
    #[On('update-tag-list')]
    public function loadTags(): void
    {
        $this->tags = Auth::user()->tags()->orderBy('created_at', 'desc')->get();
    }

    //======================================================================
    // バリデーションルール
    //======================================================================
    protected function rules(): array
    {
        return [
            'wordName' => ['string', 'max:255'],
            'wordDescription' => ['nullable', 'string'],
        ];
    }

    //======================================================================
    // メソッド
    //======================================================================

    //-----------------------------------------------------
    // CRUD機能
    //-----------------------------------------------------
    # 語句の更新
    public function updateWord(): void
    {
        $validated = $this->validate();

        /** update()には引数の値とDBに登録されている値に変化がなければ、実行をスキップする機能がある。*/
        $this->word->update([
            'word_name' => $validated['wordName'],
            'description' => $validated['wordDescription'],
        ]);

        # タグのリレーションを更新
        /*sync(array id) : 引数で渡されたidの配列とword_tag(中間テーブル)のid同期する
                > word_tag:[1,3] sync:[1,5] >> 更新された結果:[1,5]
            */
        $this->word->tags()->sync($this->selectedTagIds);

        # Wordコレクションの更新イベントを発火
        $this->dispatch('update-words');
    }

    # 語句データの削除
    public function deleteWord(): void
    {
        # 現在編集中の語句をDBから削除
        Auth::user()->words()->where('id', $this->word->id)->delete();

        # 全モーダルを閉じる
        $this->dispatch('close-all-modal');

        # Wordコレクションの更新
        $this->dispatch('update-words');
    }

    //-----------------------------------------------------
    // イベントを受け取り、モーダルを開閉　words.words-list
    //-----------------------------------------------------
    # 語句のインスタンスを受け取り、その語句の詳細ページ(モーダル)を開く処理
    /**
     * - words.words-listよりイベントを受け取り、実行
     * - 引数のモデルインスタンスをクラスプロパティに代入
     * - モーダルを開くイベントを発火
     */
    #[On('send-word')]
    public function setWord(Word $word): void
    {
        # $this->wordはDBへの登録の際に使用する
        $this->word = $word;
        $this->wordName = $word->word_name;
        $this->wordDescription = $word->description;
        $this->selectedTagIds = $word->tags->pluck('id')->all();
    }
};
?>


<section class="container-md" x-data="{ showModal: false, editMode: false, tagSelectMode: false }" x-on:open-words-detail-edit-modal.window="showModal = true"
    x-on:close-all-modal.window="showModal = false">

    <!-- モーダル本体 -->
    <div x-show="showModal">
        <div class="modal d-block" tabindex="-1">
            <div class="modal-dialog modal-fullscreen">

                <div class="modal-content">

                    <!-- 一覧と編集の切り替え部 -->
                    <article x-show="!editMode">
                        <!-- ヘッダー部 -->
                        <header class="modal-header d-flex justify-content-between align-items-center p-2">

                            <!-- ヘッダー左側 -->
                            <div class="d-flex align-items-center">
                                <!--戻るボタン -->
                                <x-back-button />

                                <span class="fs-5 fw-bold">詳細</span>
                            </div>

                            <!-- ヘッダー右側 -->
                            <div class="dropdown">
                                <!-- ドロップダウン表示ボタン -->
                                <span data-bs-toggle="dropdown" class="me-2">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </span>

                                <!-- ドロップダウンメニュー -->
                                <ul class="dropdown-menu p-1">

                                    <!-- 編集ボタン -->
                                    <li x-on:click="editMode = true" class="m-1">
                                        <span><i class="bi bi-pencil me-1"></i>編集</span>
                                    </li>
                                    <!-- 削除ボタン -->
                                    <li wire:click="deleteWord" wire:confirm="本当に削除しますか？" class="m-1">
                                        <span class="text-danger"><i class="bi bi-trash me-1"></i>削除</span>

                                    </li>
                                </ul>
                            </div>
                        </header>

                        {{-- 
                        * bg-white: 背景を白にすることで、モーダル展開前のページの要素が透けることが防げる
                        --}}
                        <div class="modal-body d-flex flex-column flex-grow-1 mx-2 mb-2 bg-white">

                            <!-- 詳細モード -->
                            <div class="position-relative d-flex flex-column w-100" x-show="!editMode">

                                <!-- 語句名 -->
                                <div>
                                    <span class="fs-5 fs-bold p-0">{{ $this->wordName }}</span>
                                </div>

                                <!-- 説明フィールド -->
                                <div class="mt-3">
                                    {{-- **white-space: pre-wrap**: 改行コードをそのまま出力する 
                                    * Pタグ内での改行禁止!
                                    * > pタグ内で改行すると、画面表示時に改行が反映されてしまう
                                    --}}
                                    <p class="flex-grow-1 p-0 text-break" style="white-space: pre-wrap; ">{{ $this->wordDescription }}</p>
                                </div>

                                <!-- タグ一覧 -->
                                <div class="postion-absolute top-0 mt-3">
                                    @foreach ($this->selectedTags as $selectedTag)
                                        <span class="badge bg-secondary me-2 mb-2 p-2"
                                            wire:key="{{ $selectedTag->id }}">
                                            {{ $selectedTag->tag_name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </article>

                    <!-- 編集モード -->
                    <article x-show="editMode">
                        <!-- ヘッダー部 -->
                        <header class="modal-header d-flex justify-content-between align-items-center p-2"
                            x-show="editMode">

                            <!-- ヘッダー左側 -->
                            <div class="d-flex align-items-center">
                                <!-- 戻るボタン(編集確定ボタンの機能をもつ)-->
                                <button type="submit" form="edit-form" x-on:click="editMode = false"
                                    wire:click="updateWord"
                                    class="btn btn-link text-dark border-0 p-0 m-2">
                                    <i class="bi bi-arrow-left fs-4"></i>
                                </button>

                                <span class="fs-5 fw-bold">編集</span>
                            </div>

                            <!-- ヘッダー右側 -->
                            <div>
                                <button type="button" class="btn btn-outline-primary"
                                    x-on:click="tagSelectMode = true">
                                    <span>タグ選択</span>
                                </button>
                            </div>
                        </header>

                        <!-- ボディ部 -->
                        <div class="modal-body d-flex flex-grow-1 flex-column mx-2 mb-2">

                            <form id="edit-form" class="d-flex flex-column w-100" wire:submit.prevent="updateWord">

                                <!-- 語句フィールド $wordName -->
                                <div>
                                    <x-form-input wire:model="wordName" />
                                </div>

                                <!-- 説明フィールド $wordDescription-->
                                <div class="mt-3">
                                    <x-form-textarea wire:model="wordDescription" />
                                </div>
                            </form>

                            <!-- タグ一覧 -->
                            <div class="mt-3">
                                @foreach ($this->selectedTags as $selectedTag)
                                    <span class="badge bg-secondary me-2 mb-2 p-2" wire:key="{{ $selectedTag->id }}">
                                        {{ $selectedTag->tag_name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </article>

                    <!-- タグ選択モーダル -->
                    <section x-show="tagSelectMode">
                        <div class="modal d-block" tabindex="-1">
                            <div class="modal-dialog modal-fullscreen">
                                <div class="modal-content">

                                    <!-- ヘッダー部 -->
                                    <header class="modal-header d-flex align-items-center p-2">
                                        <!--戻るボタン-->
                                        <button type="button" class="btn btn-link text-dark border-0 p-0 m-2"
                                            x-on:click="tagSelectMode = false">
                                            <i class="bi bi-arrow-left fs-4"></i>
                                        </button>

                                        <h5 class="modal-title mb-0">タグ</h5>
                                    </header>

                                    <!-- ボディ部 -->
                                    <div class="modal-body">
                                        <!-- タグ選択リスト -->
                                        @foreach ($this->tags as $tag)
                                            <div class="form-check" wire:key="{{ $tag->id }}">
                                                <input type="checkbox" wire:model.live="selectedTagIds"
                                                    name="selectedTagIds[]" value="{{ $tag->id }}"
                                                    id="{{ $tag->id }}" class="form-check-input">

                                                <label for="{{ $tag->id }}" class="form-check-label">
                                                    {{ $tag->tag_name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                    </section>

                </div>
            </div>
        </div>
    </div>
</section>
