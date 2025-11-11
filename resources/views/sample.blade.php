<!--////////////////
    レイアウト
///////////////////-->


<!--********************
        モーダル表示の仕組み
    **********************-->

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

<!--***********************************
        グリッドシステム(Bootstrap)の使い方
    *************************************-->

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

<!--******************************************
        レイアウトを崩さずエラーメッセージを表示する
    ********************************************-->
{{-- サンプルコード --}}
<!-- 親要素 -->
<div class="position-relative">
    <!-- 入力フォーム -->
    <input type="text" wire:model="email" class="border border-secondary" placeholder="" />
</div>

<!-- エラーメッセージ -->
@error('email')
    <div class="fs-6 text-danger list-unstyled position-absolute start-0 top-100">
        {{ $message }}
    </div>
@enderror


{{-- 解説 
    ** エラーメッセージをレイアウトを無視して表示する
    * 親要素: position-relative　→　position-absoluteで配置される要素の基点
    * 子要素: position-absolute start-0 top-100　→　レイアウトに影響を与えず、要素を親要素の一番下の左端に配置する。
    > position-absolute: レイアウトに影響を与えず、一番近くにあるposition-relativeの位置を基点に要素を配置
    > start-0: 要素を左端に配置 ※startは左側を意味する
    > top-100: 要素を一番下に配置
    ※同じ階層の要素にposition-relativeを設定すると、画面外など意図しない場所に要素が表示されるので注意
    --}}

<!--/////////////
    ビュー
//////////////-->

<!--***************
        input要素
    *******************-->
{{-- 
    ** 必須
    * type: 入力の形式と種類を決定 ※デフォルト値: type="text"
    * name: サーバーに送信されるデータのキー(名前)を定義
    * value: 入力フィールドの初期値やtype="submit"で送信される値を定義
    ※wire:modelはnameとvalueの機能を併せ持つ
    * id: 要素への一意の識別子を定義する

    ** 任意
    * placeholder: 入力フィールドがからの時に表示される薄い文字を設定
    * required: フロントエンド
    * autofocus: ページがロードされたときに、自動的に指定のフィールドにフォーカスする
    * autocomplete: ブラウザの自動補完機能を制御する
    > ユーザーが過去に入力したデータや個人情報を候補として表示する 
    --}}


<!--/////////
    認証系
//////////-->
<!--************************
        ログアウト処理
    ***************************-->
<?php
        use App\Livewire\Actions\Logout;    
        
        # ログアウト処理
        public function logout(Logout $logout): void
        {
            $logout();

            $this->redirect('/', navigate: true);
        }

        /** 解説
         * 1 Logoutクラスをメソッドインジェクションで$logoutに代入し、メソッドとして利用することでクラス内にあるログアウト処理を実行する
         * 2 guestミドルウェアにより未認証のルートURL(pages.auth.register-login)にリダイレクトされる
         * 
        */
        
        ?>


    <!--************************
            エラーメッセージの表示
        ***************************-->

    <!-- フォーム -->
    <input type="text" wire:model="email">

    <!--　エラーメッセージ -->
    {{-- パターン1 単一のエラーメッセージの表示 
    * 基本構文: @error('フィールド名') {{ $message }} @enderror 
    * 内部で次の処理を行う → $errors->has('フィールド名')  ? $message = $errors->first('フィールド名') : 処理を中断(エラーは表示されない)
    *
    * ※$errors->first('フィールド名'): 指定されたフィールド名のエラーメッセージ群から、最初のエラーメッセージを取得する
    * ※エラーメッセージの順番: バリデーションルールで設定した順番にエラーメッセージが格納される
    --}}
    @error('email')
        <div>
            {{ $message }}
        </div>
    @enderror

    {{-- パターン2 複数のエラーメッセージの表示 
    * エラーメッセージをすべて表示する
    * 
    * $errors->get('フィールド名'): フィールド名に対応するすべてのエラーメッセージを取得する
    --}}
    @if($errors->has('email'))
        <ul>
            @foreach ((array) $errors->get('email') as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    @endif


    {{-- 不具合
    ** エラーをリセット前にモーダルを非表示にすると、エラーメッセージがリセットされずに次にモーダルを開くときに表示されたままになる(解決済み)
    * > Livewireの仕組みとして「DOMの変更を最小限に抑える」ように動作するため、非表示の要素の再描画を省略したり、DOMツリーの変更が追い付かないことが原因としてあげられる。
    * 結果として、モーダルを再表示したときに、エラーメッセージが表示されたままになっていた
    * 
    * 改善前のコード
        public function clearForm()
        {
            # 指定のプロパティの値を初期値で上書きする
            $this->reset(['プロパティ名']); 

            # エラーバッグで保持しているバリデーションエラーメッセージを空にする(エラーメッセージが表示されなくなる)　
            # ※エラーバッグ: validate()で発生したエラーメッセージを内部的に保持する場所
            $this->resetValidation();
            
            # モーダルを非表示にする
            $this->dispatch('close-all-modal');  
        }

        <!-- 戻るボタン -->
        <button wire:click="clearForm">　←　ここにtype="button"を追加で改善
            <i class="bi bi-arrow-left fs-4"></i>
        </button>
    
    ** 解決策: type="button"を追加することで解決した
    * > HTMLの標準機能でform要素内でtype属性が定義されていないbutton要素にtype="submit"が適用されてしまい、
    *   意図せずフォームが送信されエラーが表示されたままだった
    --}}
