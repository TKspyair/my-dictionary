{{-- TODO:
* disable属性の付与はサーバーサイドで行うようにする
--}}
@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border border-secondary']) !!}>
