@props(['title', 'xData','events','backButton'])

<div x-data="{{ $xData }}"
    @foreach ($events as $event => $action) 
        x-on:{{ $event }}.window="{{ $action }}" 
    @endforeach
    class="container-fluid"
    >
    

    <!-- モーダル本体 -->
    <div x-show="showModal">
        <div class="modal d-block" tabindex="-1">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-body">
                        <div x-show="showTitle">
                            {{ $title }}
                        </div>
                        {{ $backButton }}
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- 
- 1 Bladeコンポーネントにx-dataがあっても、Alpainは動作するか > success
- 2 @propsでのデータ受け渡し($title)　> success 
- 3 xDataの受け渡し > ok
--}}