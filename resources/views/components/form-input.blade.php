<input type="text"
    {{ $attributes
    ->class([
        'is-invalid' => $attributes->has('wire:model') && $errors->has($attributes->get('wire:model')),])
    ->merge(['class' => 'form-control']) }}
>
<!--
- d-block: 要素をブロックレベルに設定し、親要素の幅いっぱいに広げます。
- w-100: 幅を100%に設定します。d-blockと組み合わせることで、要素をフル幅にします。
- fs-6: フォントサイズを1rem（通常、ブラウザの標準フォントサイズと同じ）に設定します。
- lh-sm: 行の高さを少し狭く設定します。
- border-0: input要素の枠線をなくす
-->


@if ($attributes->has('wire:model'))
    @error($attributes->get('wire:model'))
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
@endif
<!--
$attributes : Bladeコンポーネント専用の変数、コンポーネントに渡されたすべてのHTML属性（class、id、wire:modelなど）をコレクションとして保持する
$attributes->class(['クラス名' => 条件式]) : 条件式がtrueのときに、指定のクラスを追加する
$attributes->merge(['HTML属性'　=> '値']) : 指定の属性と値を呼び出し元のHTML属性に統合する
$attributes->has('HTML属性') : 引数に属性名を文字列で受け取り、文字列の真偽判定をする
$attributes->get('HTML属性', 'デフォルト値') : 引数の属性の値を取得する、存在しない場合もエラーにならない


-->