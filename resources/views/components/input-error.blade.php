@props(['messages'])

{{-- 
* list-unstyled: li要素の先頭に表示される「・」を削除する
--}}
@if ($messages)
    <ul {{ $attributes->merge(['class' => 'fs-6 text-danger list-unstyled mt-1']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
