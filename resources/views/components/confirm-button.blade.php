<span {{ $attributes->merge() }}>
    <i class="bi bi-check2 text-success"></i>
</span>
<!--
- $attributes->merge() : 呼び出し元の属性(class、wire:~など)をコンポーネントの要素に引き継ぐ
> これがないと、アクションが反応しなくなるので注意
-->