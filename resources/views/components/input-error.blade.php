
{{-- 
* list-unstyled: li要素の先頭に表示される「・」を削除する
--}}
<input {{ $attributes->merge(['class' => 'border border-secondary']) }}>

@if ($attributes->has('wire:model'))
    @error($attributes->get('wire:model'))
        <div {{ $attributes->merge(['class' => 'position-absolute start-0 top-100 fs-6 text-danger list-unstyled']) }}>
            {{ $message }}
        </div>
    @enderror
@endif
