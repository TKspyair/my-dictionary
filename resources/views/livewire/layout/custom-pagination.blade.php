<?php
/**TODO: 
**「現在のページ」の条件式でアイコンと数値の表示が切り替わる際、もともとの要素の違いによりレイアウトが少し変化してしまう
    paddingの調整では要素の拡大・縮小に合わせて大きさが変化してしまうので対応できなかったため、他の解決法を調べる必要がある

** ページネーションの表示を3ページ以内の場合は、1,2,3と表示し、それ以上の時に1・・・4などと表示するようにした方がいいかも
*/
?>
<div>
    @if ($paginator->hasPages())
        <nav class="m-3">
            <ul class="pagination d-flex align-items-center">
                <!-- 「前へ」ボタン -->
                <li class="flex-fill mx-1">
                    @if ($paginator->onFirstPage())
                        <span class="py-1 px-2 fs-6 text-body-tertiary"> <!-- テキストの色を薄くしてボタンを押しても意味がないことを表す -->
                            <i class="bi bi-chevron-double-left"></i>
                        </span>
                    @else
                        <button type="button" class="py-1 px-2 fs-6 border-0 bg-transparent " wire:click="previousPage"
                            wire:loading.attr="disabled">
                            <i class="bi bi-chevron-double-left"></i>
                        </button>
                    @endif
                </li>

                <!-- 最初のページ -->
                <li class="flex-fill mx-1">
                    @if ($paginator->onFirstPage())
                        <span class="py-1 px-2 fs-6 border rounded border-primary">
                            1
                        </span>
                    @else
                        <button type="button" class="py-1 px-2 fs-6 border-0 bg-transparent" wire:click="resetPage" wire:loading.attr="disabled">
                            1
                        </button>
                    @endif
                </li>

                <!-- 現在のページ -->
                <li class="flex-fill mx-2">
                    @if ($paginator->onFirstPage() || $paginator->onLastPage())
                        <span class="py-1 px-2 fs-6">
                            <i class="bi bi-three-dots"></i>
                        </span>
                    @else
                        <span class="py-1 px-2 fs-6 border rounded border-primary">
                            {{ $this->getPage() }}
                        </span>
                    @endif
                </li>

                <!-- 最後のページ -->
                <li class="flex-fill mx-1">
                    @if ($paginator->onLastPage())
                        <span class="py-1 px-2 fs-6 border rounded border-primary">
                            {{ $paginator->lastPage() }}
                        </span>
                    @else
                        <button type="button" class="py-1 px-2 fs-6 border-0 bg-transparent "
                            wire:click="gotoPage({{ $paginator->lastPage() }})" wire:loading.attr="disabled">
                            {{ $paginator->lastPage() }}
                        </button>
                    @endif
                </li>

                <!-- 「次へ」ボタン -->
                <li class="flex-fill mx-1">
                    @if ($paginator->onLastPage())
                        <span class="py-1 px-2 fs-6 text-body-tertiary">
                            <i class="bi bi-chevron-double-right"></i>
                        </span>
                    @else
                        <button type="button" class="py-1 px-2 fs-6 border-0 bg-transparent " 
                            wire:click="nextPage" wire:loading.attr="disabled"> <!-- wire:loading.attr="disabled"でページ移動時にボタンを非活性化し、誤動作やエラーを防ぐ　※フォーム送信時などにも使える -->
                            <i class="bi bi-chevron-double-right"></i>
                        </button>
                    @endif
                </li>
            </ul>
        </nav>
    @endif
</div>


<!-- ページネーションについて
NOTE: ページ数の取得について
* $paginator: ページ数を配列で管理するプロパティ(Livewireで定義されている)
* lastPage(): 最後のページ番号を数値型で取得　※onLastpage()は真偽値を返すメソッドの為、使用する際は注意
*

NOTE: Livewireのアクションの定義場所
* my-dictionary\vendor\livewire\livewire\src\Features\SupportPagination\HandlesPagination.php
*

NOTE: Bootstrapクラス
* pagination: ページネーションの親要素。子要素が水平に並び、ページネーション特有のスタイル（角丸、間隔など）が適用されます。

NOTE: spanとbuttonの使い分け
    > span: アクション(wire:clickなど)を定義しない場合
    > button: アクションを定義する場合
* 適用するクラス: 
* 要素の大きさ: py-1 px-2 fs-6(最小のフォントサイズ)
* 枠線表示: border(1pxの実線) rounded(角丸) border-primary(青色) 
* 枠線・背景色非表示: border-0(枠線削除) bg-transparent(背景色透明)

NOTE: ページネーションのli要素の並べ方
* 採用候補:
    > flex-fill: 子要素の幅を制御。コンテンツ幅に関係なく、親要素の利用可能なスペースを埋める
    > justify-content-aronud: 子要素間の余白を制御。子要素はコンテンツの幅を保持する
* 結果: flex-fillを採用
* 理由:
    > 今回は「現在のページ」のように、条件式でアイコンと数値を切り替えた際に
    それぞれの要素の大きさの違いでレイアウトが崩れてしまっていた。
    それを防ぐために、要素の幅を固定する必要があったため。
-->
