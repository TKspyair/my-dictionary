<?php

use App\Models\Tag;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout; //#[Layout('layouts.words-app')]の使用
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

new #[Layout('layouts.words-app')] class extends Component 
{
    public $tags;

    public array $checkedTagIds = [];

    //初期読み込み時に実行
    public function mount()
    {
        $this->loadTags();
    }

    //　タグ一覧の更新
    #[On('tagListUpdate')]
    public function loadTags(): void
    {
        $this->tags = Auth::user()->tags()->orderBy('created_at', 'desc')->get();
    }

    //updated()は受け取りたいプロパティ名をアッパーキャメルケースにして付けることで自動で実行される
    public function updatedCheckedTagIds($value)
    {
        $this->dispatch('show-checked-tags', ['checkedTagIds' => $value])->to('pages.words.create');
        // 変数の内容をログファイルに出力
        Log::info('Checked Tag IDs:', ['value' => $value]);
    }

};
?>

<div x-data="{ showCheckList :false }" x-on:open-tag-check-list.window="showCheckList = true">
    <div x-bind:class="{ 'modal': true, 'd-block': showCheckList }" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <!--ヘッダー-->
                <div class="modal-header d-flex align-items-center">
                    <!--戻るボタン-->
                    <button type="button" class="btn  border-0 bg-transparent p-0 me-3" data-bs-dismiss="modal"
                        x-on:click="showCheckList = false">
                        <i class="bi bi-arrow-left fs-4"></i>
                    </button>

                    <h5 class="modal-title mb-0">タグ</h5>
                </div>

                <!-- モーダルボディ -->
                <div class="modal-body">

                    <!-- チェックリスト -->
                    <div>
                        @foreach ($this->tags as $tag)
                            <div class="form-check">
                                <!-- value属性でチェック時にチェックしたタグのIDをwire:modelに渡す -->
                                <input class="form-check-input" type="checkbox" name="checkedTagIds[]" value="{{ $tag->id }}" id="{{ $tag->id }}" 
                                wire:model.live="checkedTagIds">
                                <label class="form-check-label" for="{{ $tag->id }}">
                                    {{ $tag->tag_name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
