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
    // メソッド
    //======================================================================

    # 語句リストから語句のIDを受け取り、編集画面を表示する
    /**
     * - 引数のIDを元に、モデルインスタンスを取得
     */
    #[On('send-word-id')]
    public function setWord(int $wordId): void
    {
        $this->word = Word::findOrFail($wordId);
        $this->wordName = $this->word->word_name;
        $this->wordDescription = $this->word->description;
        $this->selectedTagIds = $this->word->tags->pluck('id')->all();
    }

    # 詳細画面で表示している語句を編集画面で表示する
    public function sendWordId(): void
    {   
        $this->dispatch('send-word-id', wordId: $this->word->id)->to('pages.words.edit');
    }
};
?>


<section class="container-md" x-data="{ showModal: false }" x-on:open-words-detail-modal.window="showModal = true"
    x-on:close-all-modal.window="showModal = false">

    <!-- モーダル本体 -->
    <div x-show="showModal">
        <div class="modal d-block" tabindex="-1">
            <div class="modal-dialog modal-fullscreen">

                <div class="modal-content">

                    <!-- 詳細モード -->
                    <article>
                        <!-- ヘッダー部 -->
                        <header class="modal-header d-flex justify-content-between align-items-center p-2">

                            <!-- ヘッダー左側 -->
                            <div class="d-flex align-items-center">
                                <!--戻るボタン -->
                                <x-back-button/>

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
                                    <li wire:click="sendWordId" x-on:click="$dispatch('open-words-edit-modal')" class="m-1">
                                        <span><i class="bi bi-pencil me-1"></i>編集</span>
                                    </li>
                                </ul>
                            </div>
                        </header>

                        {{-- 
                        * bg-white: 背景を白にすることで、モーダル展開前のページの要素が透けることが防げる
                        --}}
                        <div class="modal-body d-flex flex-column flex-grow-1 mx-2 mb-2 bg-white">

                            <div class="position-relative d-flex flex-column w-100">

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
                </div>
            </div>
        </div>
    </div>
    @livewire('pages.words.edit')
</section>
