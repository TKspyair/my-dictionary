<?php

/** FIXME
 * フィルタリング画面での、タグ選択ロジックを修正
 * 現在Alpainで選択したタグの表示を切り替えているが、サーバー側と連動するようにする
 */

use Carbon\Carbon; // 日付や時間を扱うLaravelのコアライブラリ(PHPのDateTimeの拡張)
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Word;
use App\Models\Tag;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;

new #[Layout('layouts.words-app')] class extends Component 
{
    //======================================================================
    // ページネーション関連
    //======================================================================
    # ページネーション機能の有効化
    use WithPagination;

    //======================================================================
    // Property(プロパティ)
    //======================================================================

    //-----------------------------------------------------
    // ソート機能関連
    //-----------------------------------------------------

    # ソート対象のカラム
    public string $sortColumn = 'created_at';

    # ソート順(昇順,降順)　NOTE: 見るのは直近で登録した語句が多いと思われるので、降順(最新順)を初期値に設定
    public string $sortDirection = 'desc';

    //-----------------------------------------------------
    // フィルター機能関連   NOTE: ユーザーの意図しないフィルタリングを防ぐため、初期値は空にする
    //-----------------------------------------------------

    # フィルタリング状態(初期値: 無効)
    public bool $isFiltered = false;

    # フィルタリングキーワード
    public string $filterWordName = '';

    # 開始日時
    public string $filterBeginDate = '';

    # 終了日時
    public string $filterEndDate = '';

    # ユーザーのもつ全タグデータ
    public $tags;

    # 選択されたタグ
    public array $selectedTagIds = [];

    //-----------------------------------------------------
    // 初期化関連
    //-----------------------------------------------------

    public function mount()
    {
        $this->loadTags();
    }
    # タグ一覧の更新
    #[On('update-tag-list')]
    public function loadTags(): void
    {
        $this->tags = Auth::user()->tags()->orderBy('created_at', 'desc')->get();
    }

    //-----------------------------------------------------
    // 語句(Wordモデル)へのクエリ  //NOTE: Wordモデルへのクエリをこの算出プロパティにまとめる
    //-----------------------------------------------------
    /** NOTE: #[Computed]について
     * #[Computed]の動作タイミング
     * - 1 プロパティにアクセスがあったとき
     * - 2 依存するプロパティ($sortColumnなど)に変更があったとき
     *
     * 上記の仕組みにより、ソーティングやフィルタリングの際に自動で語句リストのデータが更新される
     */

    #[Computed]
    #[On('update-words')]
    public function Words()
    {
        # ユーザーのもつ語句の全データを取得
        $query = Auth::user()->words();

        # フィルタリングが適用されていれば実行
        if ($this->isFiltered) {
            # キーワードによるフィルタリング
            if ($this->filterWordName) {
                // LIKE検索で部分一致を行う (大文字小文字を区別しないデータベース設定を想定)
                $query->where('word_name', 'like', '%' . $this->filterWordName . '%');
            }

            /** NOTE: whereDate()について
             * whereDate(string カラム名, string 比較演算子, string 日付の値(YYYY-mm-dd))
             * 採用理由:
             * > created_atは日付と時刻のデータをもち、where()で日付を比較しようとすると時刻部分も考慮され意図しない結果になることがあるため
             *   時刻データを切り捨て、日時データのみを取得するwhereDate()を使用する
             */
            # 開示日によるフィルタリング
            if ($this->filterBeginDate) {
                $query->whereDate('created_at', '>=', $this->filterBeginDate);
            }
            # 終了日によるフィルタリング
            if ($this->filterEndDate) {
                $query->whereDate('created_at', '<=', $this->filterEndDate);
            }

            # 選択したタグをもつ語句のみにフィルタリング
            /**
             ** wherehas('tags', ...)
             * リレーション先のテーブル(Tagsテーブル)のレコードに基づいて、関連するタグのidをもつ親モデル(Wordモデル)を絞り込む
             * ※tags :Wordモデルで定義されているTagモデルのリレーションメソッド
             *
             ** whereIn('tags.id', $this->selectedTagIds)
             * tagsテーブルのidが選択されたタグのidのいずれかの値と一致するものを絞り込む
             */
            if ($this->selectedTagIds) {
                $query->wherehas('tags', function ($q) {
                    $q->whereIn('tags.id', $this->selectedTagIds);
                });
            }
        }

        return $query->orderBy($this->sortColumn, $this->sortDirection)->paginate(15);
    }

    
    //======================================================================
    // method(メソッド)
    //======================================================================

    //-----------------------------------------------------
    // 語句詳細モーダルとの連携
    //-----------------------------------------------------

    /**　sendWordInstance(Word $word)
     * - 語句リストの語句名をクリック時に実行
     * - クリックした語句名のモデルインスタンスをwords.detail-editに送信
     */
    public function sendWord(Word $word): void
    {   
        $this->dispatch('send-word', word: $word)->to('pages.words.detail-edit');
    }

    //-----------------------------------------------------
    // ソート機能
    //-----------------------------------------------------
    # 指定のカラムでソートする
    public function sortBy(string $column)
    {
        if ($this->sortColumn === $column) {
            # 現在のソートカラムと同じなら方向(降順、昇順)を反転
            $this->sortDirection = $this->sortDirection === 'desc' ? 'asc' : 'desc';
        } else {
            # 異なるカラムなら、引数をソートカラムに設定し、降順でソート
            $this->sortColumn = $column;
            $this->sortDirection = 'desc';
        }

        $this->resetPage();
    }

    //-----------------------------------------------------
    // フィルター機能関連
    //-----------------------------------------------------

    # フィルタリング適用
    public function applyFilter()
    {
        #　フィルター機能有効化
        $this->isFiltered = true;

        // 最初のページに戻る
        $this->resetPage();

        // Words()は、#[Computed]によりfilterWordNameなどの関連するプロパティの値が変更されると自動で実行されるため不要
    }

    # フィルタリングのクリア　※#[Renderless]: この時にプロパティがクリアされても、DOMの更新はおこらない
    #[Renderless]
    public function clearFilter()
    {
        // フィルタリングプロパティを初期化
        $this->isFiltered = false;
        $this->filterWordName = '';
        $this->filterBeginDate = '';
        $this->filterEndDate = '';
        $this->selectedTagIds = [];

    }
}; ?>

<article>
    <!-- ヘッダー部 -->
    <header class="d-flex justify-content-between align-items-center">
        <span class="fs-6 fw-bold">語句一覧</span>

        <nav class="d-flex justify-content-end align-items-center">

            <!-- ソート機能 -->
            <section class="dropdown pe-2">
                <!-- ソートマーク -->
                <button type="button" class="me-2 border-0 bg-transparent" data-bs-toggle="dropdown">
                    {{-- dropdown-toggleは不要なアイコンが表示されてしまうため使用しない --}}

                    <i class="bi bi-chevron-expand fs-4"></i>
                </button>

                <!-- ドロップダウンメニュー -->
                {{-- 
                NOTE: 
                * dropdawn-menuにスタイルを当てることで大きさを調整している(min-widthを上書きしないと調整不可)
                --}}
                <ul class="dropdown-menu" style="min-width: 50px !important;">

                    <!-- 登録順 -->
                    <li class="dropdown-item p-1" wire:click="sortBy('created_at')">
                        {{-- NOTE: 選択すると文字色と背景色が変化し,選択したことを視覚的にわかりやすくする --}}
                        <span @class([
                            'mx-2 fs-6 py-1 px-2',
                            'text-white bg-primary border-0 rounded' =>
                                $this->sortColumn === 'created_at',
                        ])>登録順
                        </span>

                        <!-- ソート方向を示す矢印  ※ソートカラムが選択されているときのみ表示 -->
                        @if ($this->sortColumn === 'created_at')
                            @if ($this->sortDirection === 'desc')
                                <!-- 降順 -->
                                <i class="bi bi-arrow-down"></i>
                            @else
                                <!-- 昇順 -->
                                <i class="bi bi-arrow-up"></i>
                            @endif
                        @endif
                    </li>

                    <!-- 名前順 -->
                    <li class="dropdown-item p-1" wire:click="sortBy('word_name')">
                        {{-- NOTE: 選択すると文字色と背景色が変化し,選択したことを視覚的にわかりやすくする --}}
                        <span @class([
                            'mx-2 fs-6 py-1 px-2',
                            'text-white bg-primary border-0 rounded' =>
                                $this->sortColumn === 'word_name',
                        ])>名前順
                        </span>

                        <!-- ソート方向を示す矢印  ※ソートカラムが選択されているときのみ表示-->
                        @if ($this->sortColumn === 'word_name')
                            @if ($this->sortDirection === 'desc')
                                <!-- 降順 -->
                                <i class="bi bi-arrow-down"></i>
                            @else
                                <!-- 昇順 -->
                                <i class="bi bi-arrow-up"></i>
                            @endif
                        @endif
                    </li>
                </ul>
            </section>

            <!-- フィルター機能 -->
            <section>
                <!-- オフキャンバス開閉ボタン -->
                <button type="button" class="border-0 bg-transparent" data-bs-toggle="offcanvas"
                    data-bs-target="#words.words-list-offcanvas">
                    <i class="bi bi-funnel fs-4"></i>
                </button>

                <!-- オフキャンバス -->
                <div class="offcanvas offcanvas-bottom rounded-top-4" style="height: 80vh !important; "tabindex="-1"
                    id="words.words-list-offcanvas"> {{-- オフキャンバスを開く data-bs-toggle="offcanvas" data-bs-target="#[指定のid] --}}
                    <!-- ヘッダー部 -->
                    <div class="offcanvas-header">
                        <span class="offcanvas-title fs-5 fw-bold" id="offcanvas">フィルター</span>
                    </div>

                    <!-- ボディ部 -->
                    <div class="offcanvas-body">
                        <form wire:submit="applyFilter">

                            <!-- 語句名フィルタリング -->
                            <div class="d-flex flex-column">
                                <span class="fs-6 fw-bold">キーワード</span>

                                <!-- 語句フィルタリングフォーム -->
                                <div class="mt-2">
                                    <input type="text" wire:model="filterWordName" autocomplete="off">
                                </div>
                                <div>

                                    <!-- 登録日時フィルタリング -->
                                    <div class="d-flex flex-column mt-4">
                                        <span class="fs-6 fw-bold">登録日時</span>

                                        <!-- 日時選択フォーム -->
                                        {{-- pt-1は日時選択フォームの枠線が上のspan要素に打ち消されることを防ぐために使用 --}}
                                        <div class="mt-2 pt-1">
                                            <!-- ～の日時 -->
                                            <input type="date" class="fs-6" wire:model="filterBeginDate">
                                            <!-- wire:modelが値を管理するため、value属性は不要 -->

                                            <span class="fs-6">～</span>

                                            <!-- ～までの日時 -->
                                            <input type="date" class="fs-6" wire:model="filterEndDate">
                                        </div>
                                    </div>

                                    <!-- タグフィルタリング -->
                                    <div class="d-flex flex-column mt-4">
                                        <span class="fs-6 fw-bold">タグ</span>

                                        <!-- タグ選択 -->
                                        {{-- 
                                        NOTE:
                                        * flex-wrapでタグを左詰めで自動改行するように表示する
                                        --}}
                                        <div class="d-flex flex-wrap mt-3">
                                            @foreach ($this->tags as $tag)
                                                <div class="me-3 mb-3" x-data="{ isSelected: false }"
                                                    wire:key="{{ $tag->id }}">
                                                    {{-- NOTE:  タグ選択の仕組み
                                            * 選択したタグのidをselectedTagIdsに格納する
                                            * value属性で選択したタグのidを値とする
                                            * input要素にbtn-checkを適用し、チェックボックスを非表示にlabel要素をボタンとして扱う
                                            !! wire:modelでliveモディファイアを使用しない
                                                > チェックボックスをクリックすると、語句リストが自動更新されオフキャンバスが閉じてしまうなど意図しない動作を引き起こす
                                            
                                            --}}
                                                    <input type="checkbox" name="selectedTagIds[]"
                                                        value="{{ $tag->id }}" id="{{ $tag->id }}"
                                                        wire:model="selectedTagIds" class="btn-check">
                                                    {{-- 
                                            NOTE:　
                                            * 選択すると背景と文字の色が変わり、視覚的に選択したことを示す 
                                            * x-on:clickで押すたびに状態を反転させる式を使用することで、切り替えを実現している
                                            --}}
                                                    <label for="{{ $tag->id }}"
                                                        class="btn border-0 rounded fs-6 py-1 px-2"
                                                        x-on:click="isSelected = !isSelected"
                                                        x-bind:class="isSelected ? 'bg-primary text-white' : 'bg-body-secondary'">
                                                        {{ $tag->tag_name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>

                                    </div>

                                    {{-- TODO: ポケポケのフィルター機能のように、常にオフキャンバスの下側に固定するようにする --}}
                                    <div class="position-fixed start-50 translate-middle-x w-100" style="bottom: 30px;">
                                        <div class="d-flex justify-content-center">
                                            <!-- フィルタリング条件確定ボタン -->
                                            <button type="submit"
                                                class="text-white border-0 rounded-3 bg-primary py-2 px-3">
                                                <span class="fs-6">絞り込み</span>
                                            </button>
                                        </div>

                                        <div class="position-relative mt-2 w-100" style="height: 50px;">

                                            <!-- 閉じるボタン -->
                                            <button type="button" 
                                                class="position-absolute top-50 start-50 translate-middle btn rounded-circle border-0 bg-danger text-white"
                                                style="z-index: 10;"
                                                data-bs-toggle="offcanvas"
                                                data-bs-target="#words.words-list-offcanvas"
                                                wire:click="clearFilter">
                                                <i class="bi bi-x-lg"></i>
                                            </button>

                                            <!-- クリアボタン -->
                                            <div class="position-absolute top-50 translate-middle-y" style="left: 30vh;">
                                                <button type="button"
                                                    class="btn border-0 rounded-2 bg-body-secondary ms-3"
                                                    wire:click="clearFilter">
                                                    <span>クリア</span>
                                                </button>
                                            </div>

                                        </div>
                                    </div>
                        </form>
                    </div>
                </div>
            </section>
        </nav>
    </header>

    <!-- 語句一覧部 -->
    <section class="mt-1">
        <ul class="list-group position-relative">
            @foreach ($this->Words as $word)
                <li class="list-group-item d-flex justify-content-between align-items-center"
                    wire:key="{{ $word->id }}">
                    {{-- idのみをメソッドに渡す --}}
                    <button wire:click="sendWord({{ $word }})"
                        class="btn btn-link text-dark border-0 p-0 mb-0 text-decoration-none">
                        {{ $word->word_name }}
                    </button>
                </li>
            @endforeach
        </ul>

        <!-- ページネーション部 -->
        {{-- 
        NOTE:
        * start-50とtranslate-middle-xの2つでx軸の位置を固定している
        * tranlate-middle(Bootstrap): 要素を自身の幅と高さの半分だけ逆方向に移動させることで親要素に対して完全に中央に配置できる
        * ページネーション部はもともとsection閉じタグの下に配置していたが、要素の大きさの問題で期待通りの場所に配置できなかったため、section要素内に配置している
        --}}

        <div class="position-absolute start-50 translate-middle-x mt-3">
            {{ $this->Words->links('livewire/layout/custom-pagination') }} <!-- 黄色破線はエディタのエラー -->
        </div>
    </section>

</article>
