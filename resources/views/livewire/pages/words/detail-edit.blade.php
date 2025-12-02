<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Word;
use App\Models\Tag;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On; // #[On('wordsUpdated')]の使用
use Livewire\Volt\Component;

new #[Layout('layouts.words-app')] class extends Component 
{
    //======================================================================
    // プロパティ
    //======================================================================

    # words.word-listから送信された語句インスタンスを格納
    public Word $word;

    # $this->word->word_nameを格納
    public ?string $wordName = '';

    # $this->word->descriptionを格納
    public ?string $wordDescription = '';

    # $wordに紐づくtagsコレクション
    public $selectedTags;

    public array $selectedTagIds = [];

    //======================================================================
    // 初期化
    //======================================================================
    public function mount()
    {
        //nullだとfoeeach文がエラーを起こすため、空のコレクションを格納
        $this->selectedTags = collect();
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
        # ロードしたときの値(モデルのプロパティ)を現在のプロパティ(このクラスのプロパティ)で更新
        $this->word->word_name = $this->wordName;
        $this->word->description = $this->wordDescription;

        # 指定した属性のいずれかに変更があれば true を返す
        /**
         * isDarty(): モデルプロパティの値がモデルがDBから取得された時とから変更があるか真偽判定する(変更有: true)
         *
         */
        $contentChanged = $this->word->isDirty(['word_name', 'description']);

        # タグの変更チェック (多対多リレーションのため配列比較が必要)
        $currentTagIds = $this->word->tags->pluck('id')->sort()->values()->all();
        $tagsChanged = $currentTagIds !== $this->selectedTagIds;

        # 変更が一つもない場合、処理をスキップ
        if (!$contentChanged && !$tagsChanged) {
            return;
        }

        # 一つでも変更があれば、更新処理

        $this->validate();

        $this->word->update([
            'word_name' => $validate['wordName'],
            'description' => $validate['wordDescription'],
        ]);

        # タグのリレーションを更新
        /*sync(array id) : 引数で渡されたidの配列とword_tag(中間テーブル)のid同期する
                > word_tag:[1,3] sync:[1,5] >> 更新された結果:[1,5]
            */
        $this->word->tags()->sync($this->selectedTags->pluck('id')->all());

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
    /** openWordDetailModal()
     * - words.words-listよりイベントを受け取り、実行
     * - 引数のモデルインスタンスをクラスプロパティに代入
     * - モーダルを開くイベントを発火
     * empty()を使用しない理由 : empty() 引数が存在しないか、空のときにtrueを返す
     * > モデルインスタンスはプロパティが空でも、オブジェクトが存在する(空とみなされないためempty()だとfalseになる)
     * >> empty()のチェックをすり抜けてしまうため、nullチェック(![引数])を行う
     */
    # 語句のインスタンスを受け取り、その語句の詳細ページ(モーダル)を開く処理
    #[On('send-word')]
    public function openWordDetailModal(Word $word): void
    {
        if (!$word) {
            return;
        }

        $this->word = $word;
        $this->wordName = $this->word->word_name;
        $this->wordDescription = $this->word->description;
        $this->selectedTags = $this->word->tags;
        
        $this->dispatch('open-words-detail-and-edit-modal');
    }

    //-----------------------------------------------------
    // タグ選択関連 tags.check-listとのみ連携
    //-----------------------------------------------------
    # 選択されたタグのid(配列型)を渡す
    /** sendSelectedTagIds(): 既に紐づけられているタグをtags.check-list内で選択済みにする
     * 一覧モードから編集モードへの切り替え時に実行
     * 
    */
    public function sendSelectedTagIds()
    {
        # チェックしたTagインスタンスをidのみの配列にする
        $this->selectedTagIds = $this->selectedTags->pluck('id')->all();

        $this->dispatch('send-selected-tag-ids', selectedTagIds: $this->selectedTagIds)
            ->to('pages.tags.check-list');
    }

    # 選択されたタグのidを配列型で受け取り、コレクション型に変換する from tags.check-list
    #[On('return-selected-tag-ids')]
    public function loadSelectedTags(array $selectedTagIds)
    {
        # 引数がnullまたは空なら、処理を中断する
        if (empty($selectedTagIds)) {
            $this->selectedTags = collect(); //空のコレクションを返す
            return;
        }

        # Tagコレクションの更新
        $this->selectedTags = Tag::whereIn('id', $selectedTagIds)->get();
    }
};
?>


<section class="container-md" x-data="{ showModal: false, editMode: false }" x-on:open-words-detail-and-edit-modal.window="showModal = true"
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
                                    <p class="flex-grow-1 p-0 text-break">{{ $this->wordDescription }}</p>
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
                                {{-- 
                                * ※ x-back-buttonは使用しない
                                * > x-back-buttonにはモーダルを閉じる機能があるが、このボタンはあくまで編集モードから一覧モードに切り替える機能のため
                                --}}
                                <button type="submit" form="edit-form" x-on:click="editMode = false"
                                    class="btn btn-link text-dark border-0 p-0 m-2">
                                    <i class="bi bi-arrow-left fs-4"></i>
                                </button>

                                <span class="fs-5 fw-bold">編集</span>
                            </div>

                            <!-- ヘッダー右側 -->
                            <div>
                                <button type="button" class="btn btn-outline-primary" x-on:click="$dispatch('open-tags-check-list')"
                                    wire:click="sendSelectedTagIds">
                                    <span>タグ選択</span>
                                </button>
                                <!-- タグ選択リストモーダル -->
                                @livewire('pages.tags.check-list')
                            </div>
                        </header>

                        <!-- ボディ部 -->
                        <div class="modal-body d-flex flex-grow-1 flex-column mx-2 mb-2">

                            <form id="edit-form" class="d-flex flex-column w-100"
                                wire:submit.prevent="updateWord">

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
                </div>
            </div>
        </div>
    </div>
</section>
