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
    // Property
    //======================================================================

    # words.word-listから送信された語句インスタンスを格納
    public Word $word;

    # $this->word->word_nameを格納
    public ?string $wordName = '';

    # $this->word->descriptionを格納
    public ?string $wordDescription = '';

    # $wordに紐づくtagsコレクション
    public $checkedTagColl;

    public array $checkedTagIds = [];

    # 初期読込時に実行
    public function mount()
    {
        //nullだとfoeeach文がエラーを起こすため回避
        $this->checkedTagColl = collect();
    }

    //======================================================================
    // バリデーションルール
    //======================================================================
    protected function rules(): array
    {
        return [
            'wordName' => ['required', 'string', 'max:255', Rule::unique('words', 'word_name')->ignore(optional($this->word))],
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
            'wordName.required' => '語句名は必須です。',
            'wordName.string' => '語句名は文字列で入力してください。',
            'wordName.max' => '語句名は255文字以内で入力してください。',
            'wordName.unique' => 'その語句名は既に存在します。別の語句名を入力してください。',

            'wordDescription.string' => '説明は文字列で入力してください。',
        ];
    }

    //======================================================================
    // Event Listenner(イベントリスナー)
    //======================================================================

    /** openWordDetailModal()
     * - words.words-listよりイベントを受け取り、実行
     * - 引数のモデルインスタンスをクラスプロパティに代入
     * - モーダルを開くイベントを発火
     * empty()を使用しない理由 : empty() 引数が存在しないか、空のときにtrueを返す
     * > モデルインスタンスはプロパティが空でも、オブジェクトが存在する(空とみなされないためempty()だとfalseになる)
     * >> empty()のチェックをすり抜けてしまうため、nullチェック(![引数])を行う
     */
    #[On('send-word-instance')]
    public function openWordDetailModal(Word $word): void
    {
        # ガード句
        if (!$word) {
            return;
        }
        $this->word = $word;
        $this->wordName = $this->word->word_name;
        $this->wordDescription = $this->word->description;
        $this->checkedTagColl = $this->word->tags;
        $this->dispatch('open-words-detail-and-edit-modal');
    }

    //======================================================================
    // Exchange with tags.check-list
    //======================================================================

    public function sendCheckedTagIds()
    {
        # チェックしたTagインスタンスをidのみの配列にする
        $this->checkedTagIds = $this->checkedTagColl->pluck('id')->all();

        $this->dispatch('dispatch-checked-tag-ids', checkedTagIds: $this->checkedtagIds)->to('pages.tags.check-list');
    }

    #[On('dispatch-checked-tag-ids')]
    public function loadCheckedTags(array $checkedTagIds)
    {
        # 引数がnullまたは空なら、処理を中断する
        if (empty($checkedTagIds)) {
            $this->checkedTagColl = collect(); //空のコレクションを返す
            return;
        }

        # Tagコレクションの更新
        $this->checkedTagColl = Tag::whereIn('id', $checkedTagIds)->get();
    }

    //======================================================================
    // CRUD機能
    //======================================================================

    public function updateWord(): void
    {
        $this->word->update([
            'word_name' => $this->wordName,
            'description' => $this->wordDescription,
        ]);

        // タグのリレーションを更新
        $this->word->tags()->sync($this->checkedTagColl->pluck('id')->all());
        /*sync(array id) : 引数で渡されたidの配列とword_tag(中間テーブル)のid同期する
            > word_tag:[1,3] sync:[1,5] >> 更新された結果:[1,5]
        */

        # Wordコレクションの更新
        $this->dispatch('update-words');
    }

    public function deleteWord(): void
    {
        # 現在編集中の語句をDBから削除
        Auth::user()->words()->where('id', $this->word->id)->delete();

        # モーダルを閉じる
        $this->dispatch('close-all-modal');

        # Wordコレクションの更新
        $this->dispatch('update-words');
    }
};
?>


<section class="container-lg" x-data="{ showModal: false, editMode: false }" x-on:open-words-detail-and-edit-modal.window="showModal = true"
    x-on:close-all-modal.window="showModal = false">

    <!-- モーダル本体 -->
    <div x-show="showModal">
        <div class="modal d-block" tabindex="-1">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">

                    <header class="modal-header p-2">

                        <!-- 一覧モード用ヘッダー -->
                        <!-- w-100がないとarticle要素の幅が小さくなるので注意(子要素のjustify-content-betweenが機能しない) -->
                        <article x-show="!editMode" class="w-100">
                            <div class="d-flex justify-content-between align-items-center p-0">

                                <div class="d-flex align-items-center">
                                    <!--戻るボタン-->
                                    <x-back-button />

                                    <!-- タイトル -->
                                    <h5 class="m-0">詳細</h5>
                                </div>

                                <!-- ドロップダウンメニュー -->
                                <div class="dropdown">
                                    <span data-bs-toggle="dropdown" class="me-2">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </span>
                                    <ul class="dropdown-menu p-1">

                                        <!-- 編集ボタン クリックで編集モードON-->
                                        <li x-on:click="editMode = true" class="m-1">
                                            <span><i class="bi bi-pencil me-1"></i>編集</span>
                                        </li>

                                        <!-- 削除ボタン -->
                                        <li wire:click="deleteWord" wire:confirm="本当に削除しますか？" class="m-1">
                                            <span><i class="bi bi-trash me-1"></i>削除</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </article>

                        <!-- 編集モード用ヘッダー -->
                        <article x-show="editMode" class="w-100">
                            <div class="d-flex align-items-center p-0 pb-2">
                                <!--戻るボタン-->
                                <button x-on:click="editMode = false" class="btn btn-link text-dark border-0 p-0 m-2">
                                    <i class="bi bi-arrow-left fs-4"></i>
                                </button>
                                <!-- タイトル -->
                                <h5 class="m-0">編集</h5>
                            </div>
                        </article>
                    </header>

                    <div class="modal-body">

                        <!-- 詳細モード -->
                        <article x-show="!editMode">

                            <!-- 語句名 -->
                            <div class="mt-4">
                                <h5>{{ $this->wordName }}</h5>
                            </div>

                            <!-- 説明フィールド -->
                            <div>
                                <p class="text-break">
                                    {{ $this->wordDescription }}
                                </p>
                            </div>

                            <!-- タグ一覧 -->
                            <div class="d-flex">
                                {{--  --}}
                                @foreach ($this->checkedTagColl as $checkedTag)
                                    <span wire:key="{{ $checkedTag->id }}" class="badge bg-secondary me-1">
                                        {{ $checkedTag->tag_name }}
                                    </span>
                                @endforeach

                            </div>
                        </article>

                        <!-- 編集モード -->
                        <article x-show="editMode">
                            <form wire:submit.prevent="updateWord">

                                <!-- 語句フィールド $wordName -->
                                <x-form-input wire:model="wordName" class="fs-5 fw-bold mt-4" />

                                <!-- 説明フィールド $wordDescription-->
                                <x-form-textarea wire:model="wordDescription" />

                                <!-- タグ選択コンポーネントを開く -->
                                <span x-on:click="$dispatch('open-tags-check-list')" wire:click="checkedTagIds">
                                    タグ選択
                                </span>

                                <!-- チェックしたタグ一覧 -->
                                <div class="d-flex">

                                    @foreach ($this->checkedTagColl as $checkedTag)
                                        <span class="badge bg-secondary me-1" wire:key="{{ $checkedTag->id }}">
                                            {{ $checkedTag->tag_name }}
                                        </span>
                                    @endforeach

                                </div>

                                <!-- 画面下部ボタン群 -->
                                <div class="d-flex justify-content-between mt-3">

                                    <!-- 削除ボタン -->
                                    <button wire:click="deleteWord" wire:confirm="本当にこの投稿を削除しますか？"
                                        class="btn btn-danger">削除
                                    </button>

                                    <!-- 更新ボタン -->
                                    <button type="submit" x-on:click="editMode = false" class="btn btn-primary">更新
                                    </button>
                                </div>
                            </form>
                            <!-- タグチェックリスト -->
                            @livewire('pages.tags.check-list')
                        </article>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

