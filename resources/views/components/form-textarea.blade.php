
{{-- x-dataでresize()を定義、x-initで初期読み込み時にサイズ調整、x-on:inputで入力するたびに大きさを変化させる
** resize() { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'}
* 1 $el.style.height = 'auto': 要素の大きさを初期値(min-height: 30vh;)に設定
* 2 $el.style.height = $el.scrollHeight + 'px': 要素の大きさをコンテンツ(入力値)に合わせて調整する

* x-effect: x-dataで定義したプロパティが更新されると実行
--}}
<textarea {{ $attributes->merge(['class' => 'flex-grow-1 p-0 form-control']) }} placeholder="説明" style="min-height: 30vh;"
    x-data="{ resize() { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'} }"
    x-init="resize()"
    x-on:input="resize()"
    x-on:textarea-resize="resize()">
</textarea>

{{-- JavaScriptの記法について
* x-data="{ Javascriptオブジェクト }" : プロパティやメソッドを定義する
* ※JavaScriptオブジェクト: { key: value }の形式で定義される名前付きの値の集合(プロパティやメソッドのこと)
** JavaScriptプロパティの定義方法:  { key: value }　※メソッドの定義と違いこの方法のみ
** JavaScriptメソッドの定義方法
* 標準            : メソッド名: function() { 処理 }
* アロー関数　　　 : メソッド名: () => { 処理 }
* 短縮メソッド記法 : メソッド名() { 処理 }
--}}