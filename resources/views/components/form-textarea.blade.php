<textarea {{ $attributes->merge(['class' => 'flex-grow-1 p-0 form-control']) }} placeholder="説明"></textarea>

@error($attributes->get('wire:model'))
    <div {{ $attributes->merge(['class' => 'position-absolute start-0 top-100 fs-6 text-danger list-unstyled']) }}>
        {{ $message }}
    </div>
@enderror

<!--
$attributes : Bladeコンポーネント専用の変数、コンポーネントに渡されたすべてのHTML属性（class、id、wire:modelなど）をコレクションとして保持する
$attributes->class(['クラス名' => 条件式]) : 条件式がtrueのときに、指定のクラスを追加する
$attributes->merge(['HTML属性'　=> '値']) : 指定の属性と値を呼び出し元のHTML属性に統合する
$attributes->has('HTML属性') : 引数に属性名を文字列で受け取り、文字列の真偽判定をする
$attributes->get('HTML属性', 'デフォルト値') : 引数の属性の値を取得する、存在しない場合もエラーにならない


-->