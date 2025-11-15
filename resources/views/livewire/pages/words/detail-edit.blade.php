<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Word;
use App\Models\Tag;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On; // #[On('wordsUpdated')]の使用
use Livewire\Volt\Component;

new #[Layout('layouts.words-app')] class extends Component {
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
    /*
    - ignore(モデルインスタンスorカラムの値) : Rule::uniqueメソッド、引数に指定された値をもつレコードをユニーク制約から除外する
    - optional($value) : 引数がnullなら処理を中断しnullを返す、もしnullでなければ引数をそのまま返す
    */

    protected function messages(): array
    {
        return [
            'wordName.string' => '語句名は文字列で入力してください。',
            'wordName.max' => '語句名は255文字以内で入力してください。',

            'wordDescription.string' => '説明は文字列で入力してください。',
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
    #[On('send-word-instance')]
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
    # 選択されたタグのidを配列型で渡す
    public function sendSelectedTagIds()
    {
        # チェックしたTagインスタンスをidのみの配列にする
        $this->selectedTagIds = $this->selectedTags->pluck('id')->all();

        $this->dispatch('dispatch-selected-tag-ids', selectedTagIds: $this->selectedtagIds)->to('pages.tags.check-list');
    }

    # 選択されたタグのidを配列型で受け取り、コレクション型に変換する
    #[On('dispatch-selected-tag-ids')]
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

                    <!-- ヘッダー部 -->
                    <header class="modal-header p-2">

                        <!-- 一覧モード用ヘッダー -->
                        <!-- w-100がないとarticle要素の幅が小さくなるので注意(子要素のjustify-content-betweenが機能しない) -->
                        <div class="d-flex justify-content-between align-items-center p-2" x-show="!editMode">

                            <!-- ヘッダー左側 -->
                            <div class="d-flex align-items-center">
                                <!--戻るボタン -->
                                <x-back-button/>

                                <!-- タイトル -->
                                <h5 class="m-0">詳細</h5>
                            </div>

                            <!-- ヘッダー右側 -->
                            <div class="dropdown">
                                <span data-bs-toggle="dropdown" class="me-2">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </span>

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
                        </div>

                        <!-- 編集モード用ヘッダー -->
                        <div class="d-flex align-items-center p-0 pb-2 w-100" x-show="editMode">
                            <!-- 戻るボタン ※編集画面から離れるとき、語句を更新-->
                            <button type="submit" x-on:click="editMode = false" form="edit-form"
                                class="btn btn-link text-dark border-0 p-0 m-2">
                                <i class="bi bi-arrow-left fs-4"></i>
                            </button>
                            <!-- タイトル -->
                            <h5 class="m-0">編集</h5>
                        </div>

                        <!-- タグ選択ボタン -->
                        <div>
                            <button type="button" x-on:click="$dispatch('open-tags-check-list')"
                                wire:click="selectedTagIds">
                                <span>タグ選択</span>
                            </button>
                        </div>
                    </header>

                    <div class="modal-body d-flex flex-grow-1 mx-2 mb-2">

                        <!-- 詳細モード -->
                        <article class="d-flex flex-column w-100" x-show="!editMode">

                            <!-- 語句名 -->
                            <div>
                                <span class="fs-5 fs-bold p-0">{{ $this->wordName }}</span>
                            </div>

                            <!-- 説明フィールド -->
                            <div class="d-flex flex-grow-1 mt-3">
                                <p class="flex-grow-1 p-0 text-break">
                                    {{ $this->wordDescription }}
                                </p>
                            </div>

                            <!-- タグ一覧 -->
                            <div class="d-flex">
                                {{--  --}}
                                @foreach ($this->selectedTags as $selectedTag)
                                    <span wire:key="{{ $selectedTag->id }}" class="badge bg-secondary me-1">
                                        {{ $selectedTag->tag_name }}
                                    </span>
                                @endforeach

                            </div>
                        </article>

                        <!-- 編集モード -->
                        <article x-show="editMode">
                            <form class="d-flex flex-column w-100" wire:submit.prevent="updateWord" id="edit-form">

                                <!-- 語句フィールド $wordName -->
                                <x-form-input wire:model="wordName" />

                                <!-- 説明フィールド $wordDescription-->
                                <x-form-textarea class="d-flex flex-grow-1 mt-3" wire:model="wordDescription" />


                                <!-- タグ一覧 -->
                                <div class="d-flex">

                                    @foreach ($this->selectedTags as $selectedTag)
                                        <span class="badge bg-secondary me-1 mb-1" wire:key="{{ $selectedTag->id }}">
                                            {{ $selectedTag->tag_name }}
                                        </span>
                                    @endforeach

                                </div>
                                <!-- タグチェックリスト -->
                                @livewire('pages.tags.check-list')
                        </article>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
