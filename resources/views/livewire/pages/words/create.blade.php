<?php

/** TODO: タグ選択だけしてモーダルを閉じて、再度モーダルを開くときに、選択したタグが表示されたままになるのを、clearForm内で無理やり対処している
 * →　モーダル再表示時にDOMが更新されるようになっている
 * 改善案: モーダルを閉じる前に更新されるようにしたい
 * メリット: ロジックがわかりやすくなる
 * デメリット: Alpainでモーダルを閉じれなくなり、ページ遷移速度が落ちる
 */

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Models\User;
use App\Models\Word;
use App\Models\Tag;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Gemini\Client;


new #[Layout('layouts.words-app')] class extends Component 
{
    //======================================================================
    // プロパティ
    //======================================================================
    public string $wordName = '';

    public string $wordDescription = '';

    //ユーザーのもつ全てのTagコレクション
    public $tags;

    //チェックしたタグのid
    public $selectedTagIds = [];

    # 選択されたタグのコレクション
    public $selectedTags;


    //======================================================================
    // 初期化
    //======================================================================

    public function mount()
    {
        #プロパティの宣言時には空のコレクションを入れられないので、ここで代入
        $this->selectedTags = collect();
        $this->loadTags();

    }

    // タグ一覧の更新
    #[On('update-tag-list')]
    public function loadTags(): void
    {
        $this->tags = Auth::user()->tags()->orderBy('created_at', 'desc')->get();
    }

    //======================================================================
    // バリデーション
    //======================================================================
    # バリデーションルール
    public function rules()
    {
        return [
            'wordName' => ['string', 'max:255'],
            'wordDescription' => ['string'],
        ];
    }

    //======================================================================
    // メソッド
    //======================================================================

    //-----------------------------------------------------
    // CRUD機能
    //-----------------------------------------------------

    # 語句データをDBに登録する
    public function createWord(): void
    {
        # 語句名と説明どちらも未入力なら登録処理を中断する
        if (empty($this->wordName) && empty($this->wordDescription)) {
            #  空のメッセージを削除したというメッセージを表示するイベントの発火
            /**
             * ユーザーに空のメモを削除したことを早く伝えるために、最初に実行
             * message: フラッシュメッセージの内容
             * type: フラッシュメッセージの色指定(例: info →　青)
             */
            $this->dispatch('flash-message', message: '空のメモを削除しました', type: 'dark');

            $this->clearForm();
            return;
        }

        #　説明が記述されているが語句名が空の場合、語句名を「未入力」とする
        if (empty($this->wordName) && !empty($this->wordDescription)) {
            $this->wordName = '未入力';
        }

        $validated = $this->validate();

        $word = Word::create([
            'word_name' => $validated['wordName'],
            'description' => $validated['wordDescription'],
            'user_id' => Auth::id(),
        ]);

        /**
         * attach() :
         * 1 中間テーブルへのレコード挿入: 中間テーブルに、関連付けに必要な外部キーのペア（例：word_idとtag_id）を自動的に挿入
         * 2 IDの自動取得: $word->tags()->attach(...)のように呼び出された際に、呼び出し元のモデル（この場合は $word）のIDを自動的に取得
         * attouch()によりtag_idとword_idが自動的に結び付けられ、中間テーブルに挿入される
         */
        $word->tags()->attach($this->selectedTags);

        $this->clearForm();

        $this->dispatch('update-words');
    }

    # フォームをクリア
    public function clearForm()
    {
        $this->reset(['wordName', 'wordDescription', 'selectedTagIds']);

        /** $this->selectedTagsをclearForm内でわざわざ初期化する理由
         * $this->selectedTagIdsをリセットすると、連動してupdatedSelectedTagIdsが実行され、selectedTagsもリセットされるが、
         * DOMの更新タイミングがモーダルを閉じた後のため、selectedTagsの更新がDOMに反映されない
         * (再度新規登録モーダルを開いたときにリセットしたはずのタグが表示されたままになる)
         *
         * ※wordNameとwordDescriptionはwire:modelで直接プロパティの状態を反映するため、影響を受けてないように見える
         */
        $this->selectedTags = collect();
        $this->resetValidation();
    }

    //-----------------------------------------------------
    // タグ選択関連
    //-----------------------------------------------------
    # selectedTagIdsが更新されると実行　例　タグ選択モーダルを閉じる、フォームをクリアする
    /**
     * updated[プロパティ名]():  Livewireの機能、指定のプロパティが更新されたとき自動で実行されるメソッドを定義できる
     */
    public function updatedSelectedTagIds()
    {
        # 引数がnullまたは空なら、処理を中断する
        if (empty($this->selectedTagIds)) {
            $this->selectedTags = collect();
            return;
        }

        //引数のタグidをもつタグコレクションを取得
        $this->selectedTags = Tag::whereIn('id', $this->selectedTagIds)->get();
    }

    //-----------------------------------------------------
    // Gemini関連
    //-----------------------------------------------------
    #　geminiで語句の説明を自動生成する
    /** $clientをメソッド内でのみ使用する理由
     * 1. このメソッド以外で使用しないから
     * 2. Livewireのシリアライズ(デハイドレーション)に対応しておらず、エラーが出るから
     * > Livewireのサポートしていないライブラリでクライアントインスタンスを実装していて
     * publicで定義するとシリアライズできずにエラー発生、privateで定義すると$clientがnullになりエラーが出るため、どちらも実装できない
     * 
     ** $yourApiKey
     * これはAPIキーを扱うため、メソッド内での定義にとどめる
    */
    public function generateGeminiText()
    {
        # 語句名未入力の場合、実行しない
        if(empty($this->wordName)){
            return;
        }

        # GeminiAPIキーの取得
        $yourApiKey = env('GEMINI_API_KEY');
        
        # APIと通信するクライアントのインスタンスを作成
        $client = Gemini::client($yourApiKey);

        #　出力形式の指定
        $prompt = "{$this->wordName}についての説明文を作成してください。
        出力はそのままメモに挿入するため、マークダウン形式にはしないでください。
        口上は排除して、{$this->wordName}の説明のみを出力してください。
        ";

        # 使用するGeminiのモデルを指定し、引数を元にコンテンツを生成する
        $result = $client->generativeModel(model: 'gemini-2.0-flash')->generateContent($prompt);
        $this->wordDescription = $result->text();
    }
}; ?>



<div class="container-md" x-data="{ showModal: false, tagSelectMode: false , showTooltip: true }" x-on:open-words-create-modal.window="showModal = true"
    x-on:close-all-modal.window="showModal = false">

    <!-- モーダル部 -->
    <section x-show="showModal">

        <div class="modal d-block" tabindex="-1">
            <div class="modal-dialog modal-fullscreen">

                <div class="modal-content">

                    <!-- ヘッダー部 -->
                    <header class="modal-header d-flex justify-content-between align-items-center p-2">

                        <!-- ヘッダー左側 -->
                        <div class="d-flex align-items-center">

                            <!-- 戻るボタン ※フォーム送信機能をもつ-->
                            {{-- 
                                ** form要素外にあるinput要素やsubmit属性をもつbutton要素はform要素と連動しないが、以下の属性を使用すると関連付けれる
                                * form="form要素のid": 、form属性を使用することで任意のform要素に関連付けれる
                                --}}
                            <button type="submit" form="create-word-form" x-on:click="showModal = false"
                                class="btn btn-link text-dark border-0 p-0 m-2">
                                <i class="bi bi-arrow-left fs-4"></i>
                            </button>

                            <span class="fs-5 fw-bold">新規作成</span>

                        </div>

                        <!-- ヘッダー右側 -->
                        <div>
                            <!-- タグ選択ボタン -->
                            <button type="button" class="btn btn-outline-primary" x-on:click="tagSelectMode = true">
                                <span>タグ選択</span>
                            </button>
                        </div>
                    </header>

                    <!-- ボディ部 -->
                    {{-- 
                        * mx-2 mb-2: 入力フィールドが画面端まで広がらないように制限
                        * d-flex flex-grow-1 w-100: flex-grow-1で縦方向に要素を広げ、w-100で横方向にも広げる
                        --}}
                    <div class="modal-body d-flex flex-grow-1 flex-column mx-2 mb-2 bg-white">

                        <form id="create-word-form" class="position-relative d-flex flex-column w-100" wire:submit.prevent="createWord">

                            <!-- 語句名フィールド -->
                            <div class="d-flex justify-content-between">
                                <x-form-input wire:model="wordName" />

                                <!-- 説明文自動生成ボタン -->
                                <button type="button" class="border-0 bg-transparent" 
                                    tooltip-text="説明文を自動生成"
                                    x-on:click="showTooltip = false"
                                    x-bind:class="{ 'custom-tooltip' : showTooltip }"
                                    wire:click="generateGeminiText">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </div>

                            <!-- 説明フィールド -->
                            {{-- 
                                * min-height: 30vh : textarea要素の初期表示時の大きさを設定する
                                --}}
                            <div class="mt-3">
                                <x-form-textarea wire:model="wordDescription" x-on:click="showTooltip = false"/>
                            </div>
                        

                            <!-- タグ一覧 -->
                            <div class="postion-absolute top-0 mt-3">
                                @foreach ($this->selectedTags as $selectedTag)
                                    <span class="badge bg-secondary me-2 mb-2 p-2" wire:key="{{ $selectedTag->id }}">
                                        {{ $selectedTag->tag_name }}
                                    </span>
                                @endforeach
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

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
                                <!-- 選択したタグのidをselectedTagIdsに格納する -->
                                <!-- value属性で選択したタグのidを値とし、wire:model.liveでselectedTagIdsに即時同期する -->
                                <input type="checkbox" wire:model.live="selectedTagIds" name="selectedTagIds[]"
                                    value="{{ $tag->id }}" id="{{ $tag->id }}" class="form-check-input">

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
