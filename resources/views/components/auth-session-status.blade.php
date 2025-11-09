@props(['status'])
<!-- セッションステータス(ログイン成功メッセージなど)を表示する -->
@if ($status)
    <div {{ $attributes->merge(['class' => 'fw-medium fs-6 text-success']) }}>
        {{ $status }}
    </div>
@endif
