

{{-- 
* デフォルトでtype="text"を適用
* > type属性を指定したい場合は、呼び出し元で定義
* form-controlはCSSでカスタム済み resouces/css/app.css
--}}
<input
    {{ $attributes
    ->class([
        'is-invalid' => $attributes->has('wire:model') && $errors->has($attributes->get('wire:model')),])
    ->merge(['class' => 'form-control']) }}
>

@if ($message)
    @error($attributes->get('wire:model'))
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
@endif
<!--
$attributes : Bladeコンポーネント専用の変数、コンポーネントに渡されたすべてのHTML属性（class、id、wire:modelなど）をコレクションとして保持する
$attributes->class(['クラス名' => 条件式]) : 条件式がtrueのときに、指定のクラスを追加する
$attributes->merge(['HTML属性'　=> '値']) : 指定の属性と値を呼び出し元のHTML属性に統合する
$attributes->has('HTML属性') : 引数に属性名を文字列で受け取り、文字列の真偽判定をする
$attributes->get('HTML属性', 'デフォルト値') : 引数の属性の値を取得する、存在しない場合もエラーにならない
-->