@props(['xData', 'events', 'title'])

<div x-data="{{ $xData }}" 
    @foreach ($events as $event => $action) 
        x-on:{{ $event }}.window="{{ $action }}" 
    @endforeach
    >

    <div x-show="showModal">
        {{-- モーダル --}}
        <div class="modal d-block" tabindex="-1">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">

                    {{-- ヘッダー --}}
                    <div class="modal-header d-flex align-items-center">
                        <!--戻るボタン-->
                        <span x-on:click="showModal = false" {{ $attributes->merge(['class' => 'p-0 m-2']) }}>
                            <i class="bi bi-arrow-left fs-4"></i>
                        </span>

                        <h5 class="modal-title mb-0">{{ $title }}</h5>
                    </div>

                    {{-- ボディ --}}
                    <div class="modal-body">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

















{{-- 
- $slot : 呼び出し元で指定されていないコンテンツを受け取る
- $(変数名)　例 $header : 呼び出し元で<x-slot:[指定の変数]></x-slot>とすることで、コンポーネント内の指定の箇所にコンテンツを渡せる
--}}

<!-- 呼び出し元サンプルコード
<x-my-modal>
    <x-slot:backButton>
<x-back-button data-bs-toggle="offcanvas" data-bs-target="#menu-index-offcanvas" />
</x-slot:backButton>
<x-slot:title>タグの編集</x-slot:title>
</x-my-modal>
-->
