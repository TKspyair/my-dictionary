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
    //インスタンスを格納
    public Word $word;

    //word_nameの値
    public ?string $wordName = '';

    //descriptionの値
    public ?string $wordDescription = '';

    //$wordに紐づくtagsコレクション
    public $checkedTagColl;

    public array $checkedTagIds = [];

    public function mount()
    {
        //nullだとfoeeach文がエラーを起こすため回避
        $this->checkedTagColl = collect();
    }
    
    protected function rules(): array
    {
        return [
            'wordName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('words', 'word_name')->ignore(optional($this->word)),
            ],
            'wordDescription' => [
                'nullable',
                'string',
            ]
        ];
    }
    /*
    - ignore(モデルインスタンスorカラムの値) : Rule::uniqueメソッド、引数に指定された値をもつレコードをユニーク制約から除外する
    - optional($value) : 引数がnullなら処理を中断しnullを返す、もしnullでなければ引数をそのまま返す
    */

    protected function messages(): array
    {
        return [
            //wordName
            'wordName.required' => '語句名は必須です。',
            'wordName.string' => '語句名は文字列で入力してください。',
            'wordName.max' => '語句名は255文字以内で入力してください。',
            'wordName.unique' => 'その語句名は既に存在します。別の語句名を入力してください。',
            //wordDescription
            'wordDescription.string' => '説明は文字列で入力してください。',
        ];
    }

    //words.words-listよりイベントを受け取り、実行
    #[On('dispatch-word-instance')]
    public function openWordDetailModal(Word $word): void
    {
        /*empty()を使用しない理由
        - empty() : 引数の存在しないか、空のときにtrueを返す
        > モデルインスタンスはプロパティが空でも、オブジェクトが存在する(空とみなされないためempty()だとfalseになる)
        >> empty()のチェックをすり抜けてしまうため、nullチェック(![引数])を行う
        */
        //ガード句
        if (!$word) {
            return;
        }
        $this->word = $word;
        $this->wordName = $this->word->word_name;
        $this->wordDescription = $this->word->description;
        $this->checkedTagColl = $this->word->tags;
        $this->dispatch('open-words-detail-and-edit-modal');
    }

    //チェックしたタグのidを配列で渡す　※コレクション型は$dispatchで送れないため
    public function checkedTagIds()
    {
        $this->checkedTagIds = $this->checkedTagColl->pluck('id')->all();

        $this->dispatch('dispatch-checked-tag-ids', checkedTagIds: $this->checkedtagIds)->to('pages.tags.check-list');
    }

    //tags.check-listからチェックしたタグのidを配列で受け取る
    #[On('dispatch-checked-tag-ids')]
    public function loadCheckedTags(array $checkedTagIds)
    {
        //引数がnullまたは空なら、処理を中断する(ガード句)
        if (empty($checkedTagIds)) {
            $this->checkedTagColl = collect(); //空のコレクションを返す
            return;
        }

        //チェックしたタグのidをもとに、$checkedTagCollの値を更新
        $this->checkedTagColl = Tag::whereIn('id', $checkedTagIds)->get();
    }

    //語句の更新
    public function updateWord(): void
    {
        //新しい入力値をwordsテーブルに挿入
        $this->word->update([
            'word_name' => $this->wordName,
            'description' => $this->wordDescription,
        ]);

        // タグのリレーションを更新
        $this->word->tags()->sync($this->checkedTagColl->pluck('id')->all());
        /*sync(array id) : 引数で渡されたidの配列とword_tag(中間テーブル)のid同期する
            > word_tag:[1,3] sync:[1,5] >> 更新された結果:[1,5]
        */

        // wordsテーブルの更新イベントを渡す(words/indexへ)
        $this->dispatch('update-words');
    }

    //語句の削除
    public function deleteWord(): void
    {
        // 現在編集中の語句をDBから削除
        Auth::user()->words()->where('id', $this->word->id)->delete();

        $this->dispatch('close-all-modal');

        // wordsテーブルの更新イベントを渡す
        $this->dispatch('update-words');
    }
};
?>


<section class="container-lg" x-data="{ showModal: false }" 
    x-on:open-words-edit-modal.window="showModal = true"
    x-on:close-all-modal.window="showModal = false">

    <!-- モーダル本体 -->
    <div x-show="showModal">
        <div class="modal d-block" tabindex="-1">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">

                    <!-- ボディ -->
                    <div class="modal-body">

                        <!-- 編集モード -->
                        <article>
                            <form wire:submit.prevent="updateWord">

                                <!-- ヘッダー -->
                                <header class="modal-header d-flex align-items-center p-0 pb-2">
                                    <!--戻るボタン-->
                                    <x-back-button/>

                                    <!-- タイトル -->
                                    <h5 class="m-0">編集</h5>
                                </header>

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
                                    <button type="submit" x-on:click="editWordMode = false" class="btn btn-primary">更新
                                    </button>
                                </div>
                            </form>
                            <!-- タグチェックリスト -->
                            @livewire('pages.tags.check-list')
                        </article>
                        <!-- ここまで編集モーダル -->
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
