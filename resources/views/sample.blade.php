<!--//////////////////
    モーダル表示の仕組み
////////////////////-->

<!-- 前提知識
    * モーダルはBootstrapクラスで実装している。
    * 表示の切り替えはAlpain.jsの「x-data」で状態を管理し、「x-show」で切り替えを実行する
    -->

<!-- 解説
        1 「class="modal d-block"」により、モーダルを常時表示にする。
        2　モーダル要素全体をそ「x-show」で囲み、表示の切り替えをAlpain.js単体で管理できるようにする
    -->

<!-- 意図
    * クライアント側の動きはなるべくAlpain.jsで一括管理をしたいため
    *
    -->

<div x-show="showModal">
    <div class="modal d-block" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <header class="modal-header p-2">
                </header>
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>
</div>

<!--//////////////////////
    グリッドシステム(Bootstrap)の使い方
///////////////////////-->

{{-- 
## グリッドシステムの構造

### グリッドの基本構造の遵守
Bootstrapのグリッドシステムは、以下の厳格な階層構造を要求します。
- 1  コンテナ (`container`)
- 2  行 (`row`)
- 3  列 (`col-*`)　
- 4  内容 (`form-check` など)
この構造を守らないと、横並びのレイアウト計算が正しく実行されません。

### グリッドの列
グリッドの列は全体で「12列」,「col-*」に数字を入れることで列の大きさを変更できる
col-[列全体でこの列が占める割合]
    例: col-4 12/4 = 3分の1の幅を占める
--}}

<!-- サンプルコード -->
<div class="container">
    <div class="row">
        <div class="col-4">
            Column1
        </div>
        <div class="col-4">
            Column2
        </div>
        <div class="col-4">
            Column3
        </div>
    </div>
</div>



<!--////////////////////////
    バリデーション表示の仕組み
/////////////////////////-->

<!-- フォーム -->
<input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">

<!--　エラーメッセージの表示部分 -->
@error('name')
    <div class="invalid-feedback">
        {{ $message }}
    </div>
@enderror

<!-- 解説
        * @error('プロパティ名')
    ～処理～
@enderror : 指定プロパティにバリデーションエラーが存在するときのみ、処理を実行する
        * is-invalid : Bootstrapのクラス、invalid-feedbackのトリガーになる
        * invalid-feedback : is-invalidをトリガーにして、文字列に赤字などのエラーメッセージ用のBootstrapクラスを複数適用する
    -->

<!-- 不具合
        *エラーをリセットする前にモーダルを非表示にすると、エラーメッセージがリセットされずに次にモーダルを開くときに表示されたままになる(解決済み)
        > Livewireの仕組みとして「DOMの変更を最小限に抑える」ように動作するため、非表示の要素の再描画を省略したり、DOMツリーの変更が追い付かないことが原因としてあげられる。
        結果として、モーダルを再表示したときに、エラーメッセージが表示されたままになっている

        例文
        public function clearForm()
        {
            $this->reset(['プロパティ名']);       1 指定のプロパティの値を初期値で上書きする
            $this->resetValidation();            2 エラーバッグで保持しているバリデーションエラーメッセージを空にする(エラーメッセージが表示されなくなる)　※エラーバッグ: validate()で発生したエラーメッセージを内部的に保持する場所
            $this->dispatch('close-all-modal');  3 モーダルを非表示にする
        }
        //戻るボタン
        <button wire:click="clearForm">
            <i class="bi bi-arrow-left fs-4"></i>
        </button>

        解決策: type="button"を追加することで解決した
        > HTMLの機能として、form要素内にtype属性が定義されていないボタンはすべてtype="submit"が適用されてしまう
        >> 意図せずフォームが送信されていたため、エラーメッセージが消えなかった

    -->

<!--////////////
    フォーム項目
/////////////-->

<!--
* 語句($word)
* 説明($description)
* タグ($selectedTag)

-->
